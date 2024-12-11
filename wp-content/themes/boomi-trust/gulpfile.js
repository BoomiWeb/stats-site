// Load plugins
const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const autoprefixer = require('gulp-autoprefixer');
const cleanCSS = require('gulp-clean-css');
const uglify = require('gulp-uglify');
const sourcemaps = require('gulp-sourcemaps');
const plumber = require('gulp-plumber');

// Paths
const paths = {
  styles: {
    src: './sass/**/*.scss',
    dest: './'
  },
  scripts: {
    src: './js/**/*.js',
    dest: './assets/js'
  }
};

// Compile Sass, autoprefix, and minify CSS
gulp.task('styles', function () {
  return gulp
    .src(paths.styles.src)
    .pipe(plumber())
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(autoprefixer({ cascade: false }))
    .pipe(cleanCSS())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(paths.styles.dest));
});

// Minify JS
gulp.task('scripts', function () {
  return gulp
    .src(paths.scripts.src)
    .pipe(plumber())
    .pipe(sourcemaps.init())
    .pipe(uglify())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(paths.scripts.dest));
});

// Watch files for changes
gulp.task('watch', function () {
  gulp.watch(paths.styles.src, gulp.series('styles'));
  gulp.watch(paths.scripts.src, gulp.series('scripts'));
});

// Default task
gulp.task('default', gulp.series('styles', 'scripts', 'watch'));