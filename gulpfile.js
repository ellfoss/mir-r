var gulp = require('gulp');
var pug = require('gulp-pug');
var stylus = require('gulp-stylus');
var autoprefixer = require('gulp-autoprefixer');
var browserSync = require('browser-sync');
var reload = browserSync.reload;
var plumber = require('gulp-plumber');

var paths = {
	php: './app/**/*.php',
	css: './app/**/*.css',
	pug: './app/**/*.pug',
	styl: './app/styles/*.styl',
	script: './app/scripts/*.*',
	png: './app/**/*.png',
	jpg: './app/**/*.jpg',
	font: './app/fonts/*.*'
};

gulp.task('files', function(){
	return gulp.src([paths.php, paths.css])
		.pipe(plumber())
		.pipe(gulp.dest('./dist/'))
		.pipe(reload({stream:true}));
});

gulp.task('pug', function(){
	return gulp.src(paths.pug)
		.pipe(plumber())
		.pipe(pug({
			pretty: true
		}))
		.pipe(gulp.dest('./dist/'))
		.pipe(reload({stream:true}));
});

gulp.task('stylus', function(){
	return gulp.src(paths.styl)
		.pipe(plumber())
		.pipe(stylus())
		.pipe(autoprefixer({
			browsers:['last 2 versions','Safari >= 5'],
			cascade: false
		}))
		.pipe(gulp.dest('./dist/styles/'))
		.pipe(reload({stream:true}));
});

gulp.task('script', function(){
	return gulp.src(paths.script)
		.pipe(plumber())
		.pipe(gulp.dest('./dist/scripts/'))
		.pipe(reload({stream:true}));
});

gulp.task('img', function(){
	return gulp.src([paths.png, paths.jpg])
		.pipe(plumber())
		.pipe(gulp.dest('./dist/'))
		.pipe(reload({stream:true}));
});

gulp.task('font', function(){
	return gulp.src(paths.font)
		.pipe(plumber())
		.pipe(gulp.dest('./dist/fonts/'))
		.pipe(reload({stream:true}));
});

gulp.task('browserSync', function(){
	browserSync({
		server: {
			baseDir: './dist'
		},
		open: true,
		notify: false
	});
});

gulp.task('watcher', function(){
	gulp.watch([paths.php, paths.css], ['files']);
	gulp.watch(paths.pug, ['pug']);
	gulp.watch(paths.styl, ['stylus']);
	gulp.watch(paths.script, ['script']);
	gulp.watch([paths.png, paths.jpg], ['img']);
	gulp.watch(paths.font, ['font']);
});

gulp.task('copy', ['files', 'script', 'img', 'font']);

gulp.task('build', ['copy', 'pug', 'stylus']);

gulp.task('default', ['build', 'watcher', 'browserSync']);
