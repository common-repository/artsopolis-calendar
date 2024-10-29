var gulp = require('gulp');

// Include plugins
var gulpConcat	= require('gulp-concat'),	//npm install gulp-concat
    rename 		= require('gulp-rename'),	//npm install gulp-rename
    cssmin		= require('gulp-cssmin'),	//npm install gulp-cssmin
    cssPath     = 'css/';


//====================== Combine then minnify Css files ================================//
gulp.task('ac-js-combine', function() {
    return gulp.src([
        cssPath+'artsopolis-calendar.css',
        cssPath+'styles.css',
        cssPath+'tile.css',
        cssPath+'month.css',
    ])
        .pipe(gulpConcat('app.css'))
        .pipe(gulp.dest(cssPath))
        .pipe(cssmin())
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest(cssPath));
});

// Gulp default
gulp.task('default', [
    'ac-js-combine',
]);