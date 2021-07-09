const gulp = require("gulp"),
    sass = require("gulp-sass"),
    postcss = require("gulp-postcss"),
    newer = require("gulp-newer"),
    autoprefixer = require("autoprefixer"),
    rename = require("gulp-rename"),
    cssnano = require("cssnano"),
    lodash = require("lodash"),
    concat = require("gulp-concat"),
    fileinclude = require("gulp-file-include"),
    uglify = require("gulp-uglify"),
    sourcemaps = require("gulp-sourcemaps");
const distPath = "../../Apps/Admin/View/default/";
const folder = {
    src: "./", // source files
    common: "../Common/",
    dist: distPath, // build files
    dist_scripts: distPath + "/public/scripts/", //build assets files
};
var vendor_scripts = [
    folder.src + "scripts/vendor/jquery.js",
    folder.src + "scripts/vendor/bootstrap.bundle.js",
    folder.src + "scripts/vendor/jquery.slimscroll.js",
    folder.src + "scripts/vendor/metisMenu.js",
    folder.src + "scripts/vendor/waves.js",
    folder.src + "scripts/vendor/jquery.repeater.js",
    folder.src + "scripts/vendor/jquery.dataTables.js",
    folder.src + "scripts/vendor/dataTables.bootstrap4.js",
    folder.src + "scripts/vendor/custombox.min.js",
    folder.src + "scripts/vendor/sweetalert2.js",
    folder.src + "scripts/vendor/lodash.min.js",
    folder.src + "scripts/vendor/jquery.treetable.js",
    folder.src + "scripts/vendor/spin.js",
    folder.src + "scripts/vendor/dropify.js",
    folder.src + "scripts/vendor/ladda.js",
    folder.src + "scripts/vendor/jquery.toast.js",
    folder.src + "scripts/vendor/select2.js",
    folder.src + "scripts/vendor/parsley.js",
    folder.common + "scripts/plugins/dropzone/dropzone.js",
];
/**
 * 第三方资源
 * @param {*} done
 */
function copyVendors(done) {
    const assets = {
        js: [
            "./node_modules/jquery/dist/jquery.js",
            "./node_modules/bootstrap/dist/js/bootstrap.bundle.js",
            "./node_modules/metismenu/dist/metisMenu.js",
            "./node_modules/jquery-slimscroll/jquery.slimscroll.js",
            "./node_modules/node-waves/dist/waves.js",
            "./node_modules/datatables.net/js/jquery.dataTables.js",
            "./node_modules/datatables.net-bs4/js/dataTables.bootstrap4.js",
            "./node_modules/datatables.net-responsive/js/dataTables.responsive.js",
            "./node_modules/datatables.net-responsive-bs4/js/responsive.bootstrap4.js",
            "./node_modules/datatables.net-buttons/js/dataTables.buttons.js",
            "./node_modules/datatables.net-buttons-bs4/js/buttons.bootstrap4.js",
            "./node_modules/datatables.net-buttons/js/buttons.html5.js",
            "./node_modules/datatables.net-buttons/js/buttons.flash.js",
            "./node_modules/datatables.net-buttons/js/buttons.print.js",
            "./node_modules/jquery.repeater/jquery.repeater.js",
            "./node_modules/datatables.net-keytable/js/dataTables.keyTable.js",
            "./node_modules/custombox/dist/custombox.min.js",
            "./node_modules/jquery-treetable/jquery.treetable.js",
            "./node_modules/sweetalert2/dist/sweetalert2.js",
            "./node_modules/ladda/js/spin.js",
            "./node_modules/ladda/js/ladda.js",
            "./node_modules/dropify/dist/js/dropify.js",
            "./node_modules/lodash/lodash.min.js",
            "./node_modules/parsleyjs/dist/parsley.js",
            "./node_modules/jquery-toast-plugin/src/jquery.toast.js",
            "./node_modules/select2/dist/js/select2.js",
        ],
        scss: [
            "./node_modules/bootstrap/dist/css/bootstrap.css",
            "./node_modules/datatables.net-bs4/css/dataTables.bootstrap4.css",
            "./node_modules/datatables.net-responsive-bs4/css/responsive.bootstrap4.css",
            "./node_modules/datatables.net-buttons-bs4/css/buttons.bootstrap4.css",
            "./node_modules/sweetalert2/dist/sweetalert2.css",
            "./node_modules/custombox/src/custombox.scss",
            "./node_modules/jquery-treetable/css/jquery.treetable.css",
            "./node_modules/metismenu/dist/metisMenu.css",
            "./node_modules/dropify/dist/css/dropify.css",
            "./node_modules/ladda/css/ladda.scss",
            "./node_modules/jquery-toast-plugin/src/jquery.toast.css",
            "./node_modules/select2/dist/css/select2.css",
            folder.common + "scripts/plugins/dropzone/dropzone.css",
        ],
    };

    //copying required vendors
    lodash(assets).forEach((assets, type) => {
        if (type == "scss") {
            gulp.src(assets)
                .pipe(
                    rename({
                        // rename aaa.css to _aaa.scss
                        prefix: "_",
                        extname: ".scss",
                    })
                )
                .pipe(gulp.dest(folder.src + "scss/vendor"));
        } else {
            gulp.src(assets).pipe(gulp.dest(folder.src + "scripts/vendor"));
        }
    });
    done();
}
function copyImages() {
    var out = folder.dist + "public/images";
    return gulp
        .src(folder.src + "views/public/images/*")
        .pipe(newer(out))
        .pipe(gulp.dest(out));
}
function copyFonts() {
    var out = folder.dist + "public/fonts";
    return gulp
        .src(folder.src + "views/public/fonts/*")
        .pipe(newer(out))
        .pipe(gulp.dest(out));
}
function copyHtml() {
    var out = folder.dist;
    var versionno = lodash.now();
    return gulp
        .src([folder.src + "views/**/*.html"])
        .pipe(
            fileinclude({
                prefix: "@@",
                basepath: "@file",
                indent: true,
                context: {
                    versionno: versionno,
                },
            })
        )
        .pipe(gulp.dest(out));
}
function style() {
    return gulp
        .src("./scss/*.scss")
        .pipe(sourcemaps.init())
        .pipe(sass())
        .on("error", sass.logError)
        .pipe(postcss([autoprefixer(), cssnano()]))
        .pipe(sourcemaps.write())
        .pipe(gulp.dest(folder.dist + "public/css/"));
}
function javascript() {
    var out = folder.dist_scripts;
    gulp.src(vendor_scripts)
        .pipe(sourcemaps.init())
        .pipe(concat("vendor.js"))
        // .pipe(gulp.dest(out))
        .pipe(
            rename({
                // rename app.js to app.min.js
                suffix: ".min",
            })
        )
        .pipe(uglify())
        .on("error", function (err) {
            console.log(err.toString());
        })
        .pipe(sourcemaps.write("./"))
        .pipe(gulp.dest(out));

    return (
        gulp
            .src([
                folder.common + "scripts/plugins/AjaxSelect.js",
                folder.common + "scripts/plugins/table/EditTable.js",
                folder.common + "scripts/plugins/table/CustomTable.js",
                folder.common + "scripts/plugins/Form.js",
                folder.common + "scripts/plugins/CusModal.js",
                folder.src + "scripts/core/Ui.js",
                folder.src + "scripts/app.js",
            ])
            .pipe(sourcemaps.init())
            .pipe(concat("app.js"))
            // .pipe(gulp.dest(out))
            .pipe(
                rename({
                    // rename app.js to app.min.js
                    suffix: ".min",
                })
            )
            .pipe(uglify())
            .on("error", function (err) {
                console.log(err.toString());
            })
            .pipe(sourcemaps.write("./"))
            .pipe(gulp.dest(out))
    );
}
function watch() {
    gulp.watch(
        [folder.src + "scss/*.scss", folder.src + "scss/**/*.scss"],
        style
    );
    gulp.watch(
        [folder.src + "scripts/*.js", folder.src + "scripts/**/*.js"],
        javascript
    );
    gulp.watch(
        [folder.src + "views/*.html", folder.src + "views/**/*.html"],
        copyHtml
    );

    gulp.watch(
        [folder.common + "scripts/*.js", folder.common + "scripts/**/*.js"],
        javascript
    );
}

gulp.task(
    "default",
    gulp.series(
        copyVendors,
        style,
        javascript,
        copyImages,
        copyFonts,
        copyHtml,
        watch
    ),
    function (done) {
        done();
    }
);
