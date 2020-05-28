<?php

ITSEC_Modules::set_setting( 'global', 'proxy', 'automatic' );
ITSEC_Response::reload_module( 'security-check' );
ITSEC_Response::reload_module( 'global' );
