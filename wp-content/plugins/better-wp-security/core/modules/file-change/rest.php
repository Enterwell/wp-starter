<?php

register_rest_route( 'ithemes-security/rpc', '/file-change/file-tree', [
	'args'                => [
		'directory' => [
			'type'              => 'string',
			'required'          => true,
			'sanitize_callback' => function ( $directory ) {
				if ( ! function_exists( 'get_home_path' ) ) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
				}

				$home = trailingslashit( get_home_path() );

				$directory = sanitize_text_field( $directory );
				$directory = untrailingslashit( $home ) . $directory;
				$directory = trailingslashit( realpath( $directory ) );

				if ( 0 !== strpos( $directory, $home ) ) {
					$directory = '/';
				} else {
					$directory = substr( $directory, strlen( $home ) );
				}

				return untrailingslashit( $directory );
			},
		],
	],
	'permission_callback' => 'ITSEC_Core::current_user_can_manage',

	'callback' => function ( $request ) {
		$home = trailingslashit( get_home_path() );
		$dir  = trim( $request['directory'], '/' );

		$path  = $home . $dir;
		$files = scandir( $path, SCANDIR_SORT_NONE );
		$found = [];

		foreach ( $files as $file ) {
			if ( '.' === $file || '..' === $file ) {
				continue;
			}

			if ( $dir ) {
				$id = '/' . trim( $dir, '/' ) . '/' . $file;
			} else {
				$id = '/' . $file;
			}

			$found[] = [
				'id'       => $id,
				'label'    => $file,
				'children' => is_dir( trailingslashit( $path ) . $file ),
			];
		}

		usort( $found, function ( $a, $b ) {
			if ( $a['children'] === $b['children'] ) {
				return strnatcasecmp( $a['label'], $b['label'] );
			}

			return $a['children'] ? -1 : 1;
		} );

		return $found;
	},
] );
