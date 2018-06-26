/**
 * @license Copyright 2017-2018 Cryptomarket Inc., MIT License 
 * see https://github.com/cryptomkt/prestashop-plugin/blob/master/LICENSE
 */

'use strict';

module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    clean: {
      build: ['dist'],
      dev: {
        src: ['/var/www/html/prestashop/modules/cryptomarket/'],
        options: {
          force: true
        }
      }
    },
    compress: {
      build: {
        options: {
          archive: 'dist/cryptomarket-for-prestashop.zip'
        },
        files: [{
          expand: true,
          cwd: 'dist',
          src: ['**']
        }]
      }
    },
    copy: {
      build: {
        files: [
          {src: ['backward_compatibility/**'], dest: 'dist/cryptomarket-for-prestashop/', filter: 'isFile'},
          {src: ['controllers/**'], dest: 'dist/cryptomarket-for-prestashop/', filter: 'isFile'},
          {src: ['override/**'], dest: 'dist/cryptomarket-for-prestashop/', filter: 'isFile'},
          {src: ['vendor/clue/**'], dest: 'dist/cryptomarket-for-prestashop/', filter: 'isFile'},
          {src: ['vendor/composer/**'], dest: 'dist/cryptomarket-for-prestashop/', filter: 'isFile'},
          {src: ['vendor/cryptomkt**'], dest: 'dist/cryptomarket-for-prestashop/', filter: 'isFile'},
          {src: ['vendor/php-http/**'], dest: 'dist/cryptomarket-for-prestashop/', filter: 'isFile'},
          {src: ['vendor/psr/**'], dest: 'dist/cryptomarket-for-prestashop/', filter: 'isFile'},
          {src: ['vendor/zend-framework/**'], dest: 'dist/cryptomarket-for-prestashop/', filter: 'isFile'},
          {src: ['translations/**'], dest: 'dist/cryptomarket-for-prestashop/', filter: 'isFile'},
          {src: ['upgrade/**'], dest: 'dist/cryptomarket-for-prestashop/', filter: 'isFile'},
          {src: ['views/**'], dest: 'dist/cryptomarket-for-prestashop/', filter: 'isFile'},
          {src: 'vendor/autoload.php', dest: 'dist/cryptomarket-for-prestashop/'},
          {src: 'config.xml', dest: 'dist/cryptomarket-for-prestashop/'},
          {src: 'index.php', dest: 'dist/cryptomarket-for-prestashop/'},
          {src: 'cryptomarket.php', dest: 'dist/cryptomarket-for-prestashop/'},
          {src: 'logo.gif', dest: 'dist/cryptomarket-for-prestashop/'},
          {src: 'payment.php', dest: 'dist/cryptomarket-for-prestashop/'},
          {src: 'LICENSE', dest: 'dist/cryptomarket-for-prestashop/'},
          {src: 'updater.php', dest: 'dist/cryptomarket-for-prestashop/'},
          {src: 'README.md', dest: 'dist/cryptomarket-for-prestashop/'}
        ]
      },
      dev: {
        files: [{
          expand: true,
          cwd: 'dist/cryptomarket-for-woocommerce',
          src: ['**/**'],
          dest: '/var/www/html/prestashop/modules/cryptomarket/'
        }]
      }
    },
    phpcsfixer: {
        build: {
            files: [
            {src: ['backward_compatibility/**'], dest: 'dist/cryptomarket-for-prestashop/', filter: 'isFile'},
            {src: ['controllers/**'], dest: 'dist/cryptomarket-for-prestashop/', filter: 'isFile'},
            {src: ['override/**'], dest: 'dist/cryptomarket-for-prestashop/', filter: 'isFile'},
            {src: ['vendor/**'], dest: 'dist/cryptomarket-for-prestashop/', filter: 'isFile'},
            {src: ['upgrade/**'], dest: 'dist/cryptomarket-for-prestashop/', filter: 'isFile'},
            {src: ['views/**'], dest: 'dist/cryptomarket-for-prestashop/', filter: 'isFile'},
            {src: 'cryptomarket.php', dest: 'dist/cryptomarket-for-prestashop/'},
            {src: 'payment.php', dest: 'dist/cryptomarket-for-prestashop/'},
            {src: 'updater.php', dest: 'dist/cryptomarket-for-prestashop/'}
          ]
        },
        options: {
            bin: 'vendor/bin/php-cs-fixer',
            diff: true,
            ignoreExitCode: true,
            level: 'all',
            quiet: true
        }
    },
    watch: {
      scripts: {
        files: ['src/**/**.*'],
        tasks: ['dev'],
        options: {
          spawn: false,
          atBegin: true
        },
      },
    }
  });

  // Load the plugins
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-compress');
  grunt.loadNpmTasks('grunt-contrib-copy');
  //grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-php-cs-fixer');

  // Default task(s).
  grunt.registerTask('build', ['phpcsfixer', 'clean:build', 'copy:build', 'compress:build']);
  grunt.registerTask('dev', ['build', 'clean:dev', 'copy:dev']);
  grunt.registerTask('default', 'build');
};