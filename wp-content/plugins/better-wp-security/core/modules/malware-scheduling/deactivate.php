<?php
ITSEC_Core::get_scheduler()->unschedule( 'malware-scan' );
ITSEC_Core::get_scheduler()->unschedule_single( 'malware-scan-site', null );
