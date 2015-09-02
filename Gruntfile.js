module.exports = function(grunt) {

    //var timestamp = Math.floor(new Date().getTime() / 1000);
    var timestamp = 5;

    grunt.initConfig({

        less: {
            'build-less': {
                options: {
                    compress: true,
                    yuicompress: true,

                    sourceMap: true,
                    outputSourceFiles: true,
                    sourceMapURL: 'automator.min.css.map',
                    sourceMapFilename: 'public/assets/build/' + timestamp + '/css/automator.min.css.map',
                    sourceMapBasepath: 'resources/assets/less',

                },
                src: [
                    'resources/assets/less/automator.less'
                ],
                dest: 'public/assets/build/' + timestamp + '/css/automator.min.css'
            }
        },

        uglify: {
            options: {
                mangle: false,
                sourceMap: true,
                sourceMapIncludeSources: true
            },
            'build-js': {
                src: [
                    'bower_components/bootstrap/dist/js/bootstrap.js',
                    'resources/assets/js/**/*.js'
                ],
                dest: 'public/assets/build/' + timestamp + '/js/automator.min.js'
            }
        },

        clean: {
            /**
             * Remove existing build files
             */
            'pre-build': {
                src: ['public/assets/build/']
            },
            'post-build': {
                src: []
            }
        },

        watch: {
            'build-less': {
                files: ['resources/assets/less/**/*.*'],
                tasks: ['less:build-less']
            },
            'build-js': {
                files: ['resources/assets/js/**/*.*'],
                tasks: ['uglify:build-js']
            }
        },

    });

    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('build', function() {

        // clean build folder
        grunt.task.run(['clean:pre-build']);

        // scss -> minified css
        grunt.task.run(['less:build-less']);

        // minify js
        grunt.task.run(['uglify:build-js']);

        // Save version number to be used in PHP
        grunt.task.run(['write-version']);

    });

    grunt.registerTask('write-version', function() {
        var versionFileContents = "<?php return array('version' => " + timestamp  + ");";
        var versionFilePath = 'config/assets.php';
        if (grunt.file.write(versionFilePath, versionFileContents)) {
            grunt.log.writeln('Wrote ' + versionFilePath);
        }
    });


};


