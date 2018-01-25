(function() {
	'use strict';

	var gulp = require('gulp');
	var sass = require('gulp-sass');
	var plumber = require('gulp-plumber');
	var rename = require('gulp-rename');
	var gutil = require('gulp-util');
	var sourcemaps = require('gulp-sourcemaps');
	var postcss = require('gulp-postcss');
	var autoprefixer = require('autoprefixer');
	var cssnano = require('cssnano');
	var livereload = require('gulp-livereload');
	var uglify = require('gulp-uglify');
	//var pump = require('pump');
	var cssdeclsort = require('css-declaration-sorter');
	//var browserify = require('browserify');
	//var tap = require('gulp-tap');
	//var buffer = require('gulp-buffer');

	var onError = function(err) {
		// eslint-disable-next-line no-console
		console.log('An error ocurred: ', gutil.colors.magenta(err.message));
		gutil.beep();
		this.emit('end');
	}

	function notifyLiveReload(event) {
		var fileName = require('path').relative(__dirname, event.path);
		livereload.changed(fileName);
	}

	gulp.task('sass-site', function() {
		var processors = [
			autoprefixer({browsers: ['last 2 versions']}),
			cssdeclsort({order: 'alphabetically'}),
		];
		return gulp.src('./sass/style.scss')
			.pipe(plumber({errorHandler: onError}))
			.pipe(sourcemaps.init())
			.pipe(sass({ outputStyle: 'nested' }))
			.pipe(postcss(processors))
			.pipe(sourcemaps.write())
			.pipe(rename("style.css"))
			.pipe(gulp.dest('./'))
			.pipe(livereload())
	});
	
/*
	gulp.task('sass-site', function() {
		var processors = [
			autoprefixer({stats: ['> 1%']}),
			cssdeclsort({order: 'alphabetically'}),
			cssnano(),
		];
		return gulp.src('./sass/style.scss')
			.pipe(plumber({errorHandler: onError}))
			.pipe(sourcemaps.init())
			.pipe(sass({ outputStyle: 'nested' }))
			.pipe(postcss(processors))
			.pipe(rename("style.css"))
			.pipe(gulp.dest('./'))
	});
*/
/*
	gulp.task('sass-admin', function() {
		var processors = [
			autoprefixer({browsers: ['last 2 versions']}),
		];
		return gulp.src('./sass/admin.sass')
			.pipe(plumber({errorHandler: onError}))
			.pipe(sass({ outputStyle: 'nested' }))
			.pipe(postcss(processors))
			.pipe(gulp.dest('./css/'))
	});
*/

	gulp.task('watch', ['sass'], function() {		
		livereload.listen();
		gulp.watch('./sass/**/*.scss', ['sass']);
		gulp.watch('./sass/**/*.sass', ['sass']);
		gulp.watch('./**/*.php', notifyLiveReload);
		gulp.watch('./js/src/*.js', ['js']);
	});

	gulp.task('sass', ['sass-site']);
	gulp.task('default', ['sass']);
}());

