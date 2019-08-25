@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/vendor/deployer/deployer/bin/dep
php "%BIN_TARGET%" %*