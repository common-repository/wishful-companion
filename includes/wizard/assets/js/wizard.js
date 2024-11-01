var sucessRequests = 0,
    totalRequests = 0,
    name = "";
! function (a) {
    "use strict";
    a(document).ready(function () {
        b.init(), c.init()
    });
    var b = {
            importData: {},
            allowPopupClosing: !0,
            init: function () {
                var b = this;
                this.categoriesFilter(), a(".wishful-blog-search-input").on("keyup", function () {
                    0 < a(this).val().length ? (a(".wishful-blog-demo-wrap .themes").find(".theme-wrap").hide(), a(".wishful-blog-demo-wrap .themes").find('.theme-wrap[data-name*="' + a(this).val().toLowerCase() + '"]').show()) : a(".wishful-blog-demo-wrap .themes").find(".theme-wrap").show()
                }), a(".wishful-blog-demo-wrap .theme-actions a.button").on("click", function (a) {
                    a.stopPropagation()
                }), a(".wishful-blog-open-popup").click(function (b) {
                    a(this).hasClass("active") ? (a(this).removeClass("active"), a(".install-demos-button").addClass("disabled"), a(".install-demos-button").attr("disabled", !0)) : (a(".wishful-blog-open-popup").removeClass("active"), a(this).addClass("active"), a(".install-demos-button").removeClass("disabled"), a(".install-demos-button").attr("disabled", !1))
                }), a(".install-demos-button").click(function (c) {
                    if (c.preventDefault(), a(".wishful-blog-open-popup.active").length > 0) {
                        var d = a(".wishful-blog-open-popup.active:first").data("demo-id");
                        a(".preview-" + d), a(".preview-all-" + d);
                        a(".demo-import-loader").show(), b.getDemoData(d)
                    } else window.location.href = a(this).attr("data-next_step")
                }), a(document).on("click", ".install-now", this.installNow), a(document).on("click", ".activate-now", this.activatePlugins), a(document).on("wp-plugin-install-success", this.installSuccess), a(document).on("wp-plugin-installing", this.pluginInstalling), a(document).on("wp-plugin-install-error", this.installError)
            },
            categoriesFilter: function () {
                var b = a(".wishful-blog-demo-wrap .themes").find(".theme-wrap"),
                    c = "wishful-blog-is-fadeout",
                    d = "wishful-blog-is-fadein",
                    e = 200,
                    f = function () {
                        var d = a.Deferred();
                        return b.addClass(c), setTimeout(function () {
                            b.removeClass(c).hide(), d.resolve()
                        }, e), d.promise()
                    },
                    g = function (a, c) {
                        var f = a ? '[data-categories*="' + a + '"]' : "div";
                        "all" === a && (f = "div"), b.filter(f).show().addClass("wishful-blog-is-fadein"), setTimeout(function () {
                            b.removeClass(d), c.resolve()
                        }, e)
                    },
                    h = function (b) {
                        var c = a.Deferred(),
                            d = f();
                        return d.done(function () {
                            g(b, c)
                        }), c
                    };
                a(".wishful-blog-navigation-link").on("click", function (b) {
                    b.preventDefault(), a(this).parent().siblings().removeClass("active"), a(this).parent().addClass("active");
                    var c = this.hash.slice(1),
                        d = a(".wishful-blog-demo-wrap .themes");
                    d.css("min-width", d.outerHeight());
                    var e = h(c);
                    e.done(function () {
                        d.removeAttr("style")
                    })
                })
            },
            getDemoData: function (b) {
                var c = this;
                a.ajax({
                    url: wishfulblogDemos.ajaxurl,
                    type: "get",
                    data: {
                        action: "wishful_blog_ajax_get_import_data",
                        demo_name: b,
                        security: wishfulblogDemos.wishful_blog_import_data_nonce
                    },
                    complete: function (b) {
                        a(".demo-import-loader").hide(), c.importData = a.parseJSON(b.responseText)
                    }
                }), a.ajax({
                    url: wishfulblogDemos.ajaxurl,
                    type: "get",
                    data: {
                        action: "wishful_blog_wizzard_ajax_get_demo_data",
                        demo_name: b,
                        demo_data_nonce: wishfulblogDemos.demo_data_nonce
                    },
                    complete: function (b) {
                        console.log(b), a(".wishful-blog-demo-wrap").html(b.responseText), a("html,body").animate({
                            scrollTop: a("#wishful-blog-demo-plugins").offset().top
                        }, 500), c.runPopup(b)
                    }
                })
            },
            runPopup: function (b) {
                var c = this;
                a(".wishful-blog-demo-popup-close, .wishful-blog-demo-popup-overlay").on("click", function (a) {
                    a.preventDefault(), c.allowPopupClosing === !0 && c.closePopup()
                }), a(".wishful-blog-plugins-next").on("click", function (b) {
                    b.preventDefault(), a("#wishful-blog-demo-plugins").hide(), a("#wishful-blog-demo-import-form").show()
                }), a("#wishful-blog-demo-import-form").submit(function (b) {
                    b.preventDefault();
                    var d = a(this).find('[name="wishful_blog_import_demo"]').val(),
                        e = a(this).find('[name="wishful_blog_import_demo_data_nonce"]').val(),
                        f = [];
                    a(this).find('input[type="checkbox"]').each(function () {
                        a(this).is(":checked") === !0 && f.push(a(this).attr("name"))
                    }), a(this).hide(), a(".wishful-blog-loader").show(), a("#wishful-blog-demo-import-form,#wishful-blog-demo-plugins").hide(), totalRequests = f.length, c.importContent({
                        demo: d,
                        nonce: e,
                        contentToImport: f,
                        isXML: a("#wishful_blog_import_xml").is(":checked")
                    })
                })
            },
            importContent: function (b) {
                var c, d, e = this,
                    f = (Date.now(), {
                        wishful_blog_import_demo: b.demo,
                        wishful_blog_import_demo_data_nonce: b.nonce
                    });
                if (this.allowPopupClosing = !1, a(".wishful-blog-demo-popup-close").fadeOut(), 0 === b.contentToImport.length) {
                    if (sucessRequests == totalRequests) return setTimeout(function () {
                        a(".wishful-blog-loader").hide(), a(".wishful-blog-last").show(), window.location.href = a(".wizard-install-demos-buttons-wrapper.final-step .skip-btn").attr("href")
                    }, 1e3), a.ajax({
                        url: wishfulblogDemos.ajaxurl,
                        type: "post",
                        data: {
                            action: "wishful_blog_after_import",
                            wishful_blog_import_demo: b.demo,
                            wishful_blog_import_demo_data_nonce: b.nonce,
                            wishful_blog_import_is_xml: b.isXML
                        },
                        complete: function (a) {}
                    }), this.allowPopupClosing = !0, void a(".wishful-blog-demo-popup-close").fadeIn();
                    a(".wishful-blog-loader").hide(), a(".wishful-blog-error").show(), a(".wizard-install-demos-buttons-wrapper.final-step").show()
                }
                for (var g in this.importData) {
                    var h = a.inArray(this.importData[g].input_name, b.contentToImport);
                    if (-1 !== h) {
                        c = g, b.contentToImport.splice(h, 1), f.action = this.importData[g].action;
                        break
                    }
                }
                a(".wishful-blog-import-status").append('<p class="wishful-blog-importing">' + this.importData[c].loader + "</p>");
                var i = a.ajax({
                    url: wishfulblogDemos.ajaxurl,
                    type: "post",
                    data: f,
                    complete: function (c) {
                        clearTimeout(d);
                        var f = !0;
                        if (500 === c.status || 502 === c.status || 503 === c.status) a(".wishful-blog-importing").addClass("wishful-blog-importing-failed").removeClass("wishful-blog-importing").text(wishfulblogDemos.content_importing_error + " " + c.status);
                        else if (-1 !== c.responseText.indexOf("successful import")) a(".wishful-blog-importing").addClass("wishful-blog-imported").removeClass("wishful-blog-importing"), sucessRequests++;
                        else {
                            var g = a.parseJSON(c.responseText),
                                h = "";
                            for (var i in g) h += g[i], "xml_import_error" === i && (f = !1);
                            a(".wishful-blog-importing").addClass("wishful-blog-importing-failed").removeClass("wishful-blog-importing").text(h), e.allowPopupClosing = !0, a(".wishful-blog-demo-popup-close").fadeIn()
                        }
                        f === !0 && e.importContent(b)
                    }
                });
                d = setTimeout(function () {
                    i.abort(), e.allowPopupClosing = !0, a(".wishful-blog-demo-popup-close").fadeIn(), a(".wishful-blog-importing").addClass("wishful-blog-importing-failed").removeClass("wishful-blog-importing").text(wishfulblogDemos.content_importing_error)
                }, 9e5)
            },
            closePopup: function () {
                a("html").css({
                    overflow: "",
                    "margin-right": ""
                }), a(".preview-icon").hide(), a(".preview-all").hide(), a("#wishful-blog-demo-popup-wrap").fadeOut(), setTimeout(function () {
                    a("#wishful-blog-demo-popup-content").html("")
                }, 600)
            },
            installNow: function (b) {
                b.preventDefault();
                var c = a(b.target),
                    d = a(document);
                c.hasClass("updating-message") || c.hasClass("button-disabled") || (wp.updates.shouldRequestFilesystemCredentials && !wp.updates.ajaxLocked && (wp.updates.requestFilesystemCredentials(b), d.on("credential-modal-cancel", function () {
                    var b = a(".install-now.updating-message");
                    b.removeClass("updating-message").text(wp.updates.l10n.installNow), wp.a11y.speak(wp.updates.l10n.updateCancel, "polite")
                })), wp.updates.installPlugin({
                    slug: c.data("slug")
                }))
            },
            activatePlugins: function (b) {
                b.preventDefault();
                var c = a(b.target),
                    d = c.data("init");
                c.data("slug");
                c.hasClass("updating-message") || c.hasClass("button-disabled") || (c.addClass("updating-message button-primary").html(wishfulblogDemos.button_activating), a.ajax({
                    url: wishfulblogDemos.ajaxurl,
                    type: "POST",
                    data: {
                        action: "wishful_blog_ajax_required_plugins_activate",
                        init: d
                    }
                }).done(function (a) {
                    a.success && c.removeClass("button-primary install-now activate-now updating-message").attr("disabled", "disabled").addClass("disabled").text(wishfulblogDemos.button_active)
                }))
            },
            installSuccess: function (b, c) {
                b.preventDefault();
                var d = a(".wishful-blog-plugin-" + c.slug).find(".button"),
                    e = d.data("init");
                d.removeClass("install-now installed button-disabled updated-message").addClass("updating-message").html(wishfulblogDemos.button_activating), setTimeout(function () {
                    a.ajax({
                        url: wishfulblogDemos.ajaxurl,
                        type: "POST",
                        data: {
                            action: "wishful_blog_ajax_required_plugins_activate",
                            init: e
                        }
                    }).done(function (a) {
                        a.success ? d.removeClass("button-primary install-now activate-now updating-message").attr("disabled", "disabled").addClass("disabled").text(wishfulblogDemos.button_active) : d.removeClass("updating-message")
                    })
                }, 1200)
            },
            pluginInstalling: function (b, c) {
                b.preventDefault();
                var d = a(".wishful-blog-plugin-" + c.slug),
                    e = d.find(".button");
                e.addClass("updating-message")
            },
            installError: function (b, c) {
                b.preventDefault();
                var d = a(".wishful-blog-plugin-" + c.slug);
                d.removeClass("button-primary").addClass("disabled").html(wp.updates.l10n.installFailedShort)
            }
        },
        c = {
            init: function () {
                var b;
                a(".upload_image_button").on("click", function (c) {
                    c.preventDefault();
                    var d = a(this);
                    return name = d.attr("data-name"), b ? void b.open() : (b = wp.media.frames.file_frame = wp.media({
                        title: "Choose Image",
                        button: {
                            text: "Choose Image"
                        },
                        multiple: !1
                    }), b.on("select", function () {
                        var c = b.state().get("selection").first().toJSON();
                        console.log(c), a("#" + name).val(c.id), a("#" + name + "-img").attr("src", c.url).show(), a('.remove_image_button[data-name="' + name + '"]').show()
                    }), void b.open())
                }), a(".remove_image_button").on("click", function (b) {
                    b.preventDefault();
                    var c = a(this),
                        d = c.attr("data-name");
                    return a("#" + d).val(""), a("#" + d + "-img").attr("src", "").hide(), c.hide(), !1
                }), a(".color-picker-field").length > 0 && a(".color-picker-field").each(function () {
                    a(this).wpColorPicker()
                })
            }
        }
}(jQuery);
