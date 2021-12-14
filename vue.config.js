module.exports = {
    outputDir: 'src/resources/',

    chainWebpack(config) {
        config.plugins.delete('html');
        config.plugins.delete('prefetch');
        config.plugins.delete('preload');
        config.plugins.delete('vue-loader');
        config.plugins.delete('case-sensitive-paths');
        config.plugins.delete('define');
        config.plugins.delete('friendly-errors');
    },
}
