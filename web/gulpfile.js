var gulp = require('gulp'),
    concat = require('gulp-concat'),
    uglify = require('gulp-uglify'),
    rename = require('gulp-rename'),
    sass = require('gulp-ruby-sass'),
    autoprefixer = require('gulp-autoprefixer'),
    minifyCSS = require('gulp-minify-css'),
    browserSync = require('browser-sync').create();

var DEST = 'build/';

gulp.task('scripts', function () {
    return gulp.src([
        'vendors/jquery/dist/jquery.min.js',
        'vendors/bootstrap/dist/js/bootstrap.min.js',
        'vendors/morris.js/morris.min.js',
        'vendors/raphael/raphael.min.js',
        'vendors/switchery/dist/switchery.min.js',
        'vendors/datatables.net/js/jquery.dataTables.min.js',
        'vendors/datatables.net-bs/js/dataTables.bootstrap.min.js',
        'vendors/datatables.net-buttons/js/dataTables.buttons.min.js',
        'vendors/datatables.net-buttons-bs/js/buttons.bootstrap.min.js',
        'vendors/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js',
        'vendors/datatables.net-keytable/js/dataTables.keyTable.min.js',
        'vendors/datatables.net-responsive/js/dataTables.responsive.min.js',
        'vendors/datatables.net-responsive-bs/js/responsive.bootstrap.js',
        'vendors/datatables.net-scroller/js/dataTables.scroller.min.js',
        'vendors/moment/min/moment.min.js',
        'src/js/helpers/*.js',
        'src/js/*.js'
    ])
        .pipe(concat('custom.js'))
        .pipe(gulp.dest(DEST + '/js'))
        .pipe(rename({suffix: '.min'}))
        .pipe(uglify().on('error', function(e){
            console.log(e);
        }))
        .pipe(gulp.dest(DEST + '/js'))
        .pipe(browserSync.stream());
});

// TODO: Maybe we can simplify how sass compile the minify and unminify version
var compileSASS = function (filename, options) {
    return sass('src/scss/*.scss', options)
        .pipe(autoprefixer('last 2 versions', '> 5%'))
        .pipe(concat(filename))
        .pipe(gulp.dest(DEST + '/css'))
        .pipe(browserSync.stream());
};

var compileLibCSS = function (filename) {
    return gulp.src(
        [
            'vendors/bootstrap/dist/css/bootstrap.min.css',
            'vendors/font-awesome/css/font-awesome.min.css',
            'vendors/switchery/dist/switchery.min.css',
            'vendors/datatables.net-bs/css/dataTables.bootstrap.min.css',
            'vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css',
            'vendors/datatables.net-fixedheader-bs/css/fixedHeader.bootstrap.min.css',
            'vendors/datatables.net-responsive-bs/css/responsive.bootstrap.min.css',
            'vendors/datatables.net-scroller-bs/css/scroller.bootstrap.min.css'
        ]
    )
    // Auto-prefix css styles for cross browser compatibility
    .pipe(autoprefixer('last 2 versions', '> 5%'))
    // Minify the file
    .pipe(concat(filename))
    //.pipe(minifyCSS())
    // Output
    .pipe(gulp.dest(DEST + '/css'))
    .pipe(browserSync.stream());
};

gulp.task('sass', function () {
    return compileSASS('custom.css', {});
});

gulp.task('sass-minify', function () {
    return compileSASS('custom.min.css', {style: 'compressed'});
});

gulp.task('library-minify', function () {
    return compileLibCSS('library.min.css');
});

gulp.task('browser-sync', function () {
    browserSync.init({
        server: {
            baseDir: './'
        },
        startPath: './production/index.html'
    });
});

gulp.task('watch', function () {
    // Watch .html files
    gulp.watch('production/*.html', browserSync.reload);
    // Watch .js files
    gulp.watch('src/js/*.js', ['scripts']);
    // Watch .scss files
    gulp.watch('src/scss/*.scss', ['sass', 'sass-minify']);
});

// Default Task
gulp.task('default', ['browser-sync', 'watch']);

gulp.task('all-sass', ['sass', 'sass-minify', 'library-minify']);
gulp.task('all', ['sass', 'sass-minify', 'library-minify', 'scripts']);