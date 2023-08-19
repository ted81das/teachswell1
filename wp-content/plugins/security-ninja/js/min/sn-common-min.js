"use strict";function sn_block_ui(e){jQuery("html.wp-toolbar").addClass("sn-overlay-active"),jQuery("#wpadminbar").addClass("sn-overlay-active"),jQuery("#sn_overlay .wf-sn-overlay-outer").css("height",jQuery(window).height()-200+"px"),jQuery("#sn_overlay").show(),e&&jQuery(e,"#sn_overlay").show()}function sn_fix_dialog_close(e){jQuery(".ui-widget-overlay").bind("click",(function(){jQuery("#"+e.target.id).dialog("close")}))}function sn_unblock_ui(e){jQuery("html.wp-toolbar").removeClass("sn-overlay-active"),jQuery("#wpadminbar").removeClass("sn-overlay-active"),jQuery("#sn_overlay").hide(),e&&jQuery(e,"#sn_overlay").hide()}jQuery(document).ready((function(){function do_test(e,t,s){var a=t[e];jQuery(".test_"+a).addClass("testing"),jQuery(".test_"+a+" .spinner").addClass("is-active"),jQuery(".test_"+a+" .sn-result-details").hide(),jQuery.ajax({type:"POST",url:ajaxurl,data:{_ajax_nonce:wf_sn.nonce_run_tests,testarr:t,action:"sn_run_single_test",stepid:e},dataType:"json",success:function success(e){jQuery(".test_"+a+" .spinner").removeClass("is-active"),jQuery(".test_"+a+" .wf-sn-label").replaceWith(e.data.label).fadeIn("slow"),jQuery(".test_"+a).removeClass("testing");var n=e.data.msg;e.data.details&&(n=n+" "+e.data.details),jQuery(".test_"+a+" .sn-result-details").replaceWith('<span class="sn-result-details">'+n+"</span>").fadeIn("slow"),jQuery(".test_"+a).removeClass("wf-sn-test-row-status-0").removeClass("wf-sn-test-row-status-5").removeClass("wf-sn-test-row-status-10").removeClass("wf-sn-test-row-status-null").addClass("wf-sn-test-row-status-"+e.data.status),jQuery(".test_"+a+' input[type="checkbox"]').prop("checked",!1),e.data.scores.output&&jQuery("#testscores").html(e.data.scores.output),"-1"==e.data.nexttest||parseInt(e.data.nexttest)>0&&do_test(parseInt(e.data.nexttest),t,s)}}).fail((function(e){window.console&&window.console.log&&window.console.log(e.statusCode+" "+e.statusText)}))}jQuery(document).on("click",".secnin_expand_all_details",(function(e){e.preventDefault(),jQuery("#security-ninja .sn-details a").each((function(){jQuery(this).trigger("click")}))})),jQuery(document).on("click","#run-selected-tests",(function(e){e.preventDefault(),jQuery("#run-selected-tests").attr("disabled",!0);var t=[],s="";jQuery("input[name='sntest[]']").each((function(){this.checked&&(s=jQuery(this).val(),jQuery(".test_"+s).addClass("testing"),jQuery(".test_"+s+" .spinner").addClass("is-active"),jQuery(".test_"+s+" .sn-result-details").hide(),t.push(s))})),do_test(0,t,self),jQuery("#run-selected-tests").attr("disabled",!1)})),jQuery(document).on("click","#sn-quickselect-all",(function(e){e.preventDefault(),jQuery("#security-ninja :checkbox").prop("checked",!0),jQuery("#security-ninja tr.test").fadeIn("fast")})),jQuery(document).on("click","#sn-quickselect-failed",(function(e){e.preventDefault(),jQuery("#security-ninja :checkbox").prop("checked",!1),jQuery("#security-ninja .wf-sn-test-row-status-0 :checkbox").prop("checked",!0),jQuery("#security-ninja .wf-sn-test-row-status-null").fadeOut("fast"),jQuery("#security-ninja .wf-sn-test-row-status-10").fadeOut("fast"),jQuery("#security-ninja .wf-sn-test-row-status-5").fadeOut("fast"),jQuery("#security-ninja .wf-sn-test-row-status-0").fadeIn("fast")})),jQuery(document).on("click","#sn-quickselect-warning",(function(e){e.preventDefault(),jQuery("#security-ninja :checkbox").prop("checked",!1),jQuery("#security-ninja .wf-sn-test-row-status-5 :checkbox").prop("checked",!0),jQuery("#security-ninja .wf-sn-test-row-status-null").fadeOut("fast"),jQuery("#security-ninja .wf-sn-test-row-status-10").fadeOut("fast"),jQuery("#security-ninja .wf-sn-test-row-status-0").fadeOut("fast"),jQuery("#security-ninja .wf-sn-test-row-status-5").fadeIn("fast")})),jQuery(document).on("click","#sn-quickselect-okay",(function(e){e.preventDefault(),jQuery("#security-ninja :checkbox").prop("checked",!1),jQuery("#security-ninja .wf-sn-test-row-status-10 :checkbox").prop("checked",!0),jQuery("#security-ninja .wf-sn-test-row-status-0").fadeOut("fast"),jQuery("#security-ninja .wf-sn-test-row-status-5").fadeOut("fast"),jQuery("#security-ninja .wf-sn-test-row-status-10").fadeIn("fast"),jQuery("#security-ninja .wf-sn-test-row-status-null").fadeOut("fast")})),jQuery(document).on("click","#sn-quickselect-untested",(function(e){e.preventDefault(),jQuery("#security-ninja :checkbox").prop("checked",!1),jQuery("#security-ninja .wf-sn-test-row-status-null :checkbox").prop("checked",!0),jQuery("#security-ninja .wf-sn-test-row-status-0").fadeOut("fast"),jQuery("#security-ninja .wf-sn-test-row-status-5").fadeOut("fast"),jQuery("#security-ninja .wf-sn-test-row-status-10").fadeOut("fast"),jQuery("#security-ninja .wf-sn-test-row-status-null").fadeIn("fast")})),jQuery(".wfsn-dismiss-review-notice, .wfsn-review-notice .notice-dismiss").on("click",(function(){jQuery(this).hasClass("wfsn-reviewlink")||event.preventDefault(),jQuery.post(ajaxurl,{action:"wf_sn_dismiss_review"}),jQuery(".wfsn-review-notice").slideUp().remove()})),jQuery("#test-details-dialog").dialog({dialogClass:"wp-dialog sn-dialog",modal:!0,resizable:!1,zIndex:9999,width:750,height:"auto",hide:"fade",open:function open(e,t){sn_fix_dialog_close(e,t)},close:function close(){jQuery("#test-details-dialog").html("<p>Please wait.</p>")},show:"fade",autoOpen:!1,closeOnEscape:!0}),jQuery(document).on("click",".openhelpscout",(function(){Beacon("open")}));var e=window.location.hash;if(e){var t=jQuery(window).scrollTop();jQuery("#wf-sn-tabs").find("a").removeClass("nav-tab-active"),jQuery(".wf-sn-tab").removeClass("active"),jQuery('a[href="'+e+'"]').addClass("nav-tab-active").removeClass("hidden"),jQuery(e).addClass("active"),jQuery(this).addClass("nav-tab-active"),jQuery(window).scrollTop(t),jQuery('[name="_wp_http_referer"]').val(window.location)}jQuery("#wf-sn-tabs").tabs({activate:function activate(e,t){var s=jQuery(window).scrollTop();window.location.hash=t.newPanel.attr("id"),jQuery(window).scrollTop(s)}}).fadeIn("fast"),jQuery("#tabs").tabs({activate:function activate(){jQuery.cookie("sn_tabs_selected",jQuery("#tabs").tabs("option","active"))},active:jQuery("#tabs").tabs({active:jQuery.cookie("sn_tabs_selected")})}),jQuery("#wf-sn-tabs").find("a").on("click",(function(e){e.preventDefault(),jQuery("#wf-sn-tabs").find("a").removeClass("nav-tab-active"),jQuery(".wf-sn-tab").removeClass("active");var t=jQuery(this).attr("id").replace("-tab",""),s=jQuery("#"+t);s.addClass("active"),jQuery(this).addClass("nav-tab-active"),s.hasClass("nosave")?jQuery("#submit").hide():jQuery("#submit").show();var a=jQuery(window).scrollTop();window.location.hash=t,jQuery(window).scrollTop(a),jQuery('[name="_wp_http_referer"]').val(window.location)})),jQuery(document).on("click","#wf-import-settings-button",(function(){return!!confirm("Are you sure you want to import and overwrite the current settings?")})),jQuery("#abort-scan").on("click",(function(e){e.preventDefault(),window.location.reload()})),jQuery(document).on("click","#sn_tests .sn-details a",(function(e){e.preventDefault(),jQuery(this).remove();var t=jQuery(this).data("test-id"),s=jQuery(this).data("test-status");jQuery(document).trigger("sn_test_details_dialog_open",[t,s]);var a=jQuery("#"+t+" .test_name").text(),n=jQuery("#"+t+" .test_description").html(),r;return""===a?(a="Unknown test ID",n="Help is not available for this test. Make sure you have the latest version of Security Ninja installed."):(n='<span class="ui-helper-hidden-accessible"><input type="text"></span><span class="spinner"></span>'+jQuery("#"+t+" .test_description").html(),n+='<div id="auto-fixer-content-cont"><hr><h3>Auto Fixer</h3><div id="auto-fixer-content"></div></div>'),jQuery(".tdesc-test-id-"+t).slideUp().html(n).slideDown("slow"),jQuery("."+t+".testtimedetails").prepend('<div class="spinner is-active"></div>'),jQuery.ajax({type:"POST",url:ajaxurl,data:{_ajax_nonce:wf_sn.nonce_run_tests,action:"sn_get_single_test_details",testid:t},dataType:"json",success:function success(e){jQuery("."+t+".testtimedetails .spinner").remove(),e.success&&(e.data.runtime&&jQuery("."+t+".testtimedetails .runtime").html("Runtime: "+e.data.runtime+" sec."),e.data.timestamp&&jQuery("."+t+".testtimedetails .lasttest").html("Last test: "+e.data.timestamp),e.data.timestamp&&jQuery("."+t+".testtimedetails .score").html("Score: "+e.data.score),e.data.timestamp&&jQuery("."+t+".testtimedetails .status").html("Status: "+e.data.status),jQuery("."+t+".testtimedetails").show())},error:function error(){jQuery("."+t+".testtimedetails .spinner").remove()}}),!1}))}));
//# sourceMappingURL=sn-common-min.js.map