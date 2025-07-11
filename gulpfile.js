var pkg = require('./package.json');
var inds = pkg.independents;
var gulp = require('gulp');
var uglify = require('gulp-uglify-es').default;
var babel = require('gulp-babel');
var minify = require('gulp-clean-css');
var minifyHtml = require('gulp-minify-html');
var concat = require('gulp-concat');
var rename = require('gulp-rename');
var replace = require('gulp-replace');
var header = require('gulp-header');
var del = require('del');
var gulpif = require('gulp-if');
var minimist = require('minimist');
var argv = require('minimist')(process.argv.slice(2), { default: { ver: 'all' } });
var note = ['/** <%= pkg.name %>-v<%= pkg.version %> <%= pkg.license %> License By <%= pkg.homepage %> */\n <%= js %>', { pkg: pkg, js: ';' }];
var destDir = './public/assets/libs/pear/dist';
var srcDir = './public/assets/libs/pear/src';

var task = {
  minjs: function () {
    var src = [
      srcDir + '/pear.js',
      srcDir + '/**/*.js'
      , '!' + srcDir + '/module/tinymce/tinymce/**/*.js'
      , '!' + srcDir + '/module/echarts.js'
    ];
    return gulp.src(src)
      .pipe(babel({
        presets: ['es2015'],
        compact: false
      }))
      .pipe(uglify({
        mangle: false,
        compress: true,
        output: { comments: false }
      }))
      .pipe(header.apply(null, note))
      .pipe(gulp.dest(destDir));
  },
  mincss: function () {
    var noteNew = JSON.parse(JSON.stringify(note));
    noteNew[1].js = '';
    return gulp.src([srcDir + '/**/*.css'])
      .pipe(minify({
        compatibility: 'ie8',
        paths: [srcDir, srcDir + '/css']
      }))
      .pipe(header.apply(null, noteNew))
      .pipe(gulp.dest(destDir));
  },
  minihtml: function () {
    return gulp.src([srcDir + '/**/*.html'])
      .pipe(minifyHtml({ collapseWhitespace: true, comments: false }))
      .pipe(gulp.dest(destDir));
  },
  mv: function () {
    gulp.src(srcDir + '/font/**/*.json').pipe(gulp.dest(destDir + '/font/'));
    gulp.src(srcDir + '/font/**/*.ttf').pipe(gulp.dest(destDir + '/font/'));
    gulp.src(srcDir + '/font/**/*.woff').pipe(gulp.dest(destDir + '/font/'));
    gulp.src(srcDir + '/font/**/*.woff2').pipe(gulp.dest(destDir + '/font/'));
    gulp.src(srcDir + '/module/echarts.js').pipe(gulp.dest(destDir + '/module/'));
    gulp.src(srcDir + '/module/tinymce/tinymce/**/*').pipe(gulp.dest(destDir + '/module/tinymce/tinymce/'));
  }
};

gulp.task('clear', function (cb) { return del(['./dist/*'], cb); });
gulp.task('minjs', task.minjs);
gulp.task('mincss', task.mincss);
gulp.task('minify-html', task.minihtml);
gulp.task('mv', task.mv);
gulp.task('src', function () { return gulp.src('./!**!/!*').pipe(gulp.dest(srcDir)); });
gulp.task('default', ['clear', 'src'], function () {
  for (var key in task) {
    task[key]();
  }
});