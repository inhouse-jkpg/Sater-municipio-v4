// Include gulp
var gulp = require('gulp');

// Include Our Plugins
var sass = require('gulp-sass')(require('sass'));
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var cssnano = require('gulp-cssnano');
var rename = require('gulp-rename');
var autoprefixer = require('gulp-autoprefixer');
var plumber = require('gulp-plumber');

// Compile Our Sass
gulp.task('sass-dist', async function() {
    var dist = gulp.src('source/sass/SaterModularity.scss')
            .pipe(plumber())
            .pipe(sass())
            .pipe(autoprefixer('last 2 version', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1'))
            .pipe(rename({suffix: '.min'}))
            .pipe(cssnano())
            .pipe(gulp.dest('dist/css'));
        
        return dist;
});

gulp.task('sass-dev', async function() {
    var dev = gulp.src('source/sass/SaterModularity.scss')
        .pipe(plumber())
        .pipe(sass())
        .pipe(autoprefixer('last 2 version', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1'))
        .pipe(rename({suffix: '.dev'}))
        .pipe(gulp.dest('dist/css'));

    return dev;
});

// Concatenate & Minify JS
gulp.task('scripts-dist', async function() {
    var scriptsDist = gulp.src([
            'source/js/**/*.js',
        ])
        .pipe(concat('SaterModularity.dev.js'))
        .pipe(gulp.dest('dist/js'))
        .pipe(rename('SaterModularity.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest('dist/js'));

    return scriptsDist;
});

// Watch Files For Changes
gulp.task('watch', function() {
    gulp.watch('source/js/**/*.js', gulp.series('scripts-dist'));
    gulp.watch('source/sass/**/*.scss',gulp.series('sass-dist', 'sass-dev'));
});

// Default Task
gulp.task('default', gulp.series('sass-dist', 'sass-dev', 'scripts-dist', 'watch'));

