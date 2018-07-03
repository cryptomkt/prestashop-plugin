/**
 * @license Copyright 2017-2018 Cryptomarket Inc., MIT License 
 * see https://github.com/cryptomkt/prestashop-plugin/blob/master/LICENSE
 */

'use strict';
var glob = require('glob');

module.exports = function(grunt) {
  var _files_to_build = [
            {src: ['backward_compatibility/**'], dest: 'dist/cryptomarket/', filter: 'isFile'},
            {src: ['controllers/**'], dest: 'dist/cryptomarket/', filter: 'isFile'},
            {src: ['override/**'], dest: 'dist/cryptomarket/', filter: 'isFile'},
            {src: ['vendor/clue/**'], dest: 'dist/cryptomarket/', filter: 'isFile'},
            {src: ['vendor/composer/**'], dest: 'dist/cryptomarket/', filter: 'isFile'},
            {src: ['vendor/cryptomkt/**'], dest: 'dist/cryptomarket/', filter: 'isFile'},
            {src: ['vendor/php-http/**'], dest: 'dist/cryptomarket/', filter: 'isFile'},
            {src: ['vendor/psr/**'], dest: 'dist/cryptomarket/', filter: 'isFile'},
            {src: ['vendor/zendframework/**'], dest: 'dist/cryptomarket/', filter: 'isFile'},
            {src: ['translations/**'], dest: 'dist/cryptomarket/', filter: 'isFile'},
            {src: ['upgrade/**'], dest: 'dist/cryptomarket/', filter: 'isFile'},
            {src: ['views/**'], dest: 'dist/cryptomarket/', filter: 'isFile'},
            {src: 'vendor/autoload.php', dest: 'dist/cryptomarket/'},
            {src: 'config.xml', dest: 'dist/cryptomarket/'},
            {src: 'index.php', dest: 'dist/cryptomarket/'},
            {src: 'cryptomarket.php', dest: 'dist/cryptomarket/'},
            {src: 'logo.gif', dest: 'dist/cryptomarket/'},
            {src: 'payment.php', dest: 'dist/cryptomarket/'},
            {src: 'LICENSE', dest: 'dist/cryptomarket/'}, 
            {src: 'updater.php', dest: 'dist/cryptomarket/'},
            {src: 'README.md', dest: 'dist/cryptomarket/'},
            {src: 'index.php', dest: 'dist/cryptomarket/vendor/'}
          ];

  glob('vendor/**/', function (err, res) {
    if (err) {
      console.log('Error', err);
    } else {
      res.map(function(path){
        _files_to_build.push({src: 'index.php', dest: path })
      });
    }
  });

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
          archive: 'dist/cryptomarket.zip'
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
        files: _files_to_build
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
            dir: 'dist/cryptomarket'
        },
        options: {
            configfile: '.php_cs',
            bin: 'php-cs-fixer',
            diff: true,
            ignoreExitCode: true,
            quiet: true,
            usingCache: false
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
  grunt.registerTask('build', ['clean:build', 'copy:build', 'phpcsfixer', 'compress:build']);
  grunt.registerTask('dev', ['build', 'clean:dev', 'copy:dev']);
  grunt.registerTask('default', 'build');
};