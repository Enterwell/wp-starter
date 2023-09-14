<?php

namespace iThemesSecurity\Ban_Hosts;

use iThemesSecurity\Exception\Invalid_Argument_Exception;
use iThemesSecurity\Exception\WP_Error;

final class Multi_Repository implements Source {

	/** @var Repository[] */
	private $repositories = [];

	/**
	 * Multi_Repository constructor.
	 *
	 * @param Repository ...$repositories
	 */
	public function __construct( Repository ...$repositories ) {
		foreach ( $repositories as $repository ) {
			$this->repositories[ $repository->get_slug() ] = $repository;
		}
	}

	public function find_ban_for_host( $host ) {
		return ( new Chain_Source( ...array_values( $this->repositories ) ) )->find_ban_for_host( $host );
	}

	/**
	 * Gets a list of bans.
	 *
	 * @param Filters      $filters
	 * @param Multi_Cursor $cursor
	 * @param string       $source
	 *
	 * @return Multi_Repository_Results
	 */
	public function get_bans( Filters $filters, Multi_Cursor $cursor = null, $source = '' ) {
		if ( $source ) {
			$repository = $this->repository( $source );

			if ( $filters->get_limit() && $cursor && $repo_cursor = $cursor->get_cursor( $repository ) ) {
				$filters = $filters->with_before_cursor( $repo_cursor );
			}

			$next_cursor = null;
			$results     = $repository->get_bans( $filters );

			if ( $filters->get_limit() ) {
				$next_cursor = $this->cursor();
				$next_cursor->with_cursor( $repository, Cursor::from_results( $results ) );
			}

			return new Multi_Repository_Results( $results, $next_cursor );
		}

		if ( ! $this->repositories ) {
			return new Multi_Repository_Results( [] );
		}

		if ( ! $filters->get_limit() ) {
			return new Multi_Repository_Results( array_merge( ...array_map( static function ( Repository $repository ) use ( $filters ) {
				return $repository->get_bans( $filters );
			}, $this->repositories, [] ) ) );
		}

		$count_per_repo  = $filters->get_limit() / count( $this->repositories );
		$populated_repos = $cursor ? array_filter( $this->repositories, static function ( Repository $repository ) use ( $cursor, $count_per_repo ) {
			return $cursor->get_last_count( $repository ) >= $count_per_repo;
		} ) : $this->repositories;
		$count_per_repo  = floor( $filters->get_limit() / count( $populated_repos ) );
		$extra           = $filters->get_limit() - $count_per_repo * count( $populated_repos );

		$next_cursor = $this->cursor();
		$results     = [];

		foreach ( $populated_repos as $repository ) {
			$limit = $count_per_repo;

			if ( $extra ) {
				$limit += $extra;
				$extra = 0;
			}

			$repo_filters = $filters->with_limit( $limit );

			if ( $cursor && $repo_cursor = $cursor->get_cursor( $repository ) ) {
				$repo_filters = $repo_filters->with_before_cursor( $repo_cursor );
			}

			$result      = $repository->get_bans( $repo_filters );
			$next_cursor = $next_cursor->with_cursor( $repository, Cursor::from_results( $result ) );
			$results[]   = $result;
		}

		$bans = $results ? array_merge( ...$results ) : [];

		usort( $bans, static function ( Repository_Ban $a, Repository_Ban $b ) {
			$a = $a->get_created_at();
			$b = $b->get_created_at();

			return $b->getTimestamp() - $a->getTimestamp();
		} );

		return new Multi_Repository_Results( $bans, $next_cursor );
	}

	/**
	 * Counts the list of bans.
	 *
	 * @param Filters $filters
	 * @param string  $source
	 *
	 * @return int
	 */
	public function count_bans( Filters $filters, $source = '' ) {
		if ( $source ) {
			return $this->repository( $source )->count_bans( $filters );
		}

		$count = 0;

		foreach ( $this->repositories as $repository ) {
			$count += $repository->count_bans( $filters );
		}

		return $count;
	}

	/**
	 * Gets the list of supported query filters.
	 *
	 * A list of {@see Filters} constants declaring the available options.
	 *
	 * @param string $source
	 *
	 * @return string[]
	 */
	public function get_supported_filters( $source = '' ) {
		if ( $source ) {
			return $this->repository( $source )->get_supported_filters();
		}

		$supported = [];

		foreach ( $this->repositories as $repository ) {
			$supported[] = $repository->get_supported_filters();
		}

		return $supported ? array_intersect( Filters::ALL, ...$supported ) : [];
	}

	/**
	 * Gets a Ban from the given source.
	 *
	 * @param string $source
	 * @param int    $id
	 *
	 * @return Repository_Ban|null
	 */
	public function get( $source, $id ) {
		return $this->repository( $source )->get( $id );
	}

	/**
	 * Checks if creates are supported by the given repository.
	 *
	 * @param string $source
	 *
	 * @return bool
	 */
	public function supports_create( $source ) {
		return $this->repository( $source ) instanceof Creatable;
	}

	/**
	 * Gets the JSON Schema for creating a new ban.
	 *
	 * @param string $source
	 *
	 * @return array
	 */
	public function get_creation_schema( $source ) {
		$repository = $this->repository( $source );

		if ( ! $repository instanceof Creatable ) {
			throw new Unsupported_Operation( sprintf( esc_html__( 'The ban source "%s" does not support creating a ban.', 'better-wp-security' ), $source ) );
		}

		return $repository->get_creation_schema();
	}

	/**
	 * Checks if updates are supported by the given repository.
	 *
	 * @param string $source
	 *
	 * @return bool
	 */
	public function supports_update( $source ) {
		return $this->repository( $source ) instanceof Updatable;
	}

	/**
	 * Gets the JSON Schema for creating a new ban.
	 *
	 * @param string $source
	 *
	 * @return array
	 */
	public function get_update_schema( $source ) {
		$repository = $this->repository( $source );

		if ( ! $repository instanceof Updatable ) {
			throw new Unsupported_Operation( sprintf( esc_html__( 'The ban source "%s" does not support updating a ban.', 'better-wp-security' ), $source ) );
		}

		return $repository->get_update_schema();
	}

	/**
	 * Fills a ban from request data.
	 *
	 * @param string              $source
	 * @param array               $data
	 * @param Repository_Ban|null $ban
	 *
	 * @return Repository_Ban
	 */
	public function fill( $source, array $data, Repository_Ban $ban = null ) {
		$repository = $this->repository( $source );

		if ( ! $repository instanceof Persistable ) {
			throw new Unsupported_Operation( sprintf( esc_html__( 'The ban source "%s" does not support saving bans.', 'better-wp-security' ), $source ) );
		}

		if ( $ban && $ban->get_source() !== $source ) {
			throw new Invalid_Argument_Exception( sprintf( esc_html__( 'The source "%1$s" does not match the previous ban source "%2$s".', 'better-wp-security' ), $source, $ban->get_source() ) );
		}

		return $repository->fill( $data, $ban );
	}

	/**
	 * Persists a ban to the repository.
	 *
	 * @param Repository_Ban $ban
	 *
	 * @return Repository_Ban
	 * @throws WP_Error
	 */
	public function persist( Repository_Ban $ban ) {
		$repository = $this->repository( $ban->get_source() );

		if ( ! $repository instanceof Persistable ) {
			throw new Unsupported_Operation( sprintf( esc_html__( 'The ban source "%s" does not support saving bans.', 'better-wp-security' ), $ban->get_source() ) );
		}

		return $repository->persist( $ban );
	}

	/**
	 * Checks if deletes are supported by the given repository.
	 *
	 * @param string $source
	 *
	 * @return bool
	 */
	public function supports_delete( $source ) {
		return $this->repository( $source ) instanceof Deletable;
	}

	/**
	 * Deletes a ban.
	 *
	 * @param Repository_Ban $ban
	 */
	public function delete( Repository_Ban $ban ) {
		$repository = $this->repository( $ban->get_source() );

		if ( ! $repository instanceof Deletable ) {
			throw new Unsupported_Operation( sprintf( esc_html__( 'The ban source "%s" does not support deleting a ban.', 'better-wp-security' ), $ban->get_source() ) );
		}

		$repository->delete( $ban );
	}

	/**
	 * Gets the list of repository slugs this object proxies.
	 *
	 * @return string[]
	 */
	public function get_sources() {
		return array_keys( $this->repositories );
	}

	/**
	 * Creates a cursor instance.
	 *
	 * @param string $formatted
	 *
	 * @return Multi_Cursor
	 */
	public function cursor( $formatted = '' ) {
		return new Multi_Cursor( $formatted, ...array_values( $this->repositories ) );
	}

	/**
	 * Gets the repository with the given slug.
	 *
	 * @param string $slug
	 *
	 * @return Repository
	 */
	private function repository( $slug ) {
		if ( ! isset( $this->repositories[ $slug ] ) ) {
			throw new Unknown_Source( sprintf( esc_html__( 'Unknown ban source "%s".', 'better-wp-security' ), $slug ) );
		}

		return $this->repositories[ $slug ];
	}
}
