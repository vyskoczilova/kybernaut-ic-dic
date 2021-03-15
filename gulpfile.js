'use strict';

// Dependencies
var gulp = require('gulp');
var minify = require('gulp-minify');
var wpPot = require('gulp-wp-pot'); // For generating the .pot file.
var sort = require('gulp-sort'); // Recommended to prevent unnecessary changes in pot-file.
var babel = require('gulp-babel');
var autoprefixer = require('gulp-autoprefixer');
var cleanCSS = require('gulp-clean-css');
var gcmq = require('gulp-group-css-media-queries');

// Translation related.
var text_domain             = 'woolab-ic-dic'; // Your textdomain here.
var translationFile         = 'woolab-ic-dic.pot'; // Name of the transalation file.
var packageName             = 'woolab-ic-dic'; // Package name.
var translationDestination  = './languages'; // Where to save the translation files.
var bugReport               = 'https://kybernaut.cz/kontakt/'; // Where can users report bugs.
var lastTranslator          = 'Karolína Vyskočilová <karolina@kybernaut.cz>'; // Last translator Email ID.
var team                    = 'Kybernaut <karolina@kybernaut.cz>'; // Team's Email ID.

// Watch files paths.
var projectPHPWatchFiles    = './**/*.php'; // Path to all PHP files.

// Handle JS
gulp.task("scripts", function (done) {
    gulp.src('src/js/[^_]*.js')
        .on('error', console.log)
        .pipe(babel({
            presets: [
                '@babel/preset-env',
            ]
        }))
        .pipe(minify({
            ext: {
                src: '.js',
                min: '.min.js'
            }
        }))
        .pipe(gulp.dest('assets/js/'));
    done();
});

// Minify CSS
gulp.task('styles', function (done) {
    gulp.src(['src/css/*.css'])
        .pipe(autoprefixer({
            cascade: false
        }))
        .pipe(gcmq())
        .pipe(cleanCSS({}))
        .pipe(gulp.dest('assets/css/'))
    done();
});

/**
  * WP POT Translation File Generator.
  * https://github.com/ahmadawais/WPGulp/blob/master
  *
  * * This task does the following:
  *     1. Gets the source of all the PHP files
  *     2. Sort files in stream by path or any custom sort comparator
  *     3. Applies wpPot with the variable set at the top of this file
  *     4. Generate a .pot file of i18n that can be used for l10n to build .mo file
  */
 gulp.task( 'translate', function () {
    return gulp.src( projectPHPWatchFiles )
        .pipe(sort())
        .pipe(wpPot( {
            domain        : text_domain,
            package       : packageName,
            bugReport     : bugReport,
            lastTranslator: lastTranslator,
            team          : team
        } ))
       .pipe(gulp.dest(translationDestination + '/' + translationFile ))
});


/**
 * Complete build task
 */
 gulp.task('build', gulp.series('styles', 'scripts', 'translate'));
