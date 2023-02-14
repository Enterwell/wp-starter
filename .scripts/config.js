module.exports = {
    namespace: 'EWStarter', // PHP namespace name [PascalCase]
    pluginNameForFileNames: 'ewplugin', // occurrence of plugin name in file names [kebab-case]
    pluginNameForFunctions: 'ew_plugin', // occurrence of plugin name in function names [snake_case]
    baseRoute: 'wp-ew', // API route base name (starter.local/wp-json/<wp-ew>/v1/), usually short and prefixed with ew [kebab-case]
    themeNameForFileNames: 'ew-theme', // theme folder and files occurrences name [kebab-case]
    webAppServerDomain: 'ew-wp-starter.local', // local domain name [kebab-case]
    artifactName: 'ewStarter' // Azure Pipelines artifact name, if necessary [camelCase]
};