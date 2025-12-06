// radio checked - hotspot plan
$(document).ready(function () {
	$('input[type=radio]').change(function () {

		if ($('#Time_Limit').is(':checked')) {
			$('#DataLimit').hide();
			$('#TimeLimit').show();
		}
		if ($('#Data_Limit').is(':checked')) {
			$('#TimeLimit').hide();
			$('#DataLimit').show();
		}
		if ($('#Both_Limit').is(':checked')) {
			$('#TimeLimit').show();
			$('#DataLimit').show();
		}

		if ($('#Unlimited').is(':checked')) {
			$('#Type').hide();
			$('#TimeLimit').hide();
			$('#DataLimit').hide();
		} else {
			$('#Type').show();
		}

		if ($('#Hotspot').is(':checked')) {
			$('#p').hide();
			$('#h').show();
		}
		if ($('#PPPOE').is(':checked')) {
			$('#p').show();
			$('#h').hide();
		}

	});
});
$("#Hotspot").prop("checked", true).change();


function checkIP(f, id) {
	if (f.value.length > 6) {
		$.get(appUrl + '/?_route=autoload/pppoe_ip_used&ip=' + f.value + '&id=' + id, function (data) {
			$("#warning_ip").html(data)
		});
	}
}

function checkUsername(f, id) {
	if (f.value.length > 1) {
		$.get(appUrl + '/?_route=autoload/pppoe_username_used&u=' + f.value + '&id=' + id, function (data) {
			$("#warning_username").html(data)
		});
	}
}

//auto load pool - pppoe plan
var htmlobjek;
$(document).ready(function () {
	$("#routers").change(function () {
		var routers = $("#routers").val();
		$.ajax({
			url: appUrl + "/?_route=autoload/pool",
			data: "routers=" + routers,
			cache: false,
			success: function (msg) {
				$("#pool_name").html(msg);
			}
		});
	});
});

//auto load plans data - recharge user
$(function () {
	$('input[type=radio]').change(function () {
		if ($('#Hot').is(':checked')) {
			$.ajax({
				type: "POST",
				dataType: "html",
				url: appUrl + "/?_route=autoload/server",
				success: function (msg) {
					$("#server").html(msg);
				}
			});

			$("#server").change(getAjaxAlamat);
			function getAjaxAlamat() {
				var server = $("#server").val();
				$.ajax({
					type: "POST",
					dataType: "html",
					url: appUrl + "/?_route=autoload/plan",
					data: "jenis=Hotspot&server=" + server,
					success: function (msg) {
						$("#plan").html(msg);
					}
				});
			};

		} else if ($('#POE').is(':checked')) {
			$.ajax({
				type: "POST",
				dataType: "html",
				url: appUrl + "/?_route=autoload/server",
				success: function (msg) {
					$("#server").html(msg);
				}
			});
			$("#server").change(function () {
				var server = $("#server").val();
				$.ajax({
					type: "POST",
					dataType: "html",
					url: appUrl + "/?_route=autoload/plan",
					data: "jenis=PPPOE&server=" + server,
					success: function (msg) {
						$("#plan").html(msg);
					}
				});
			});
		} else {
			$.ajax({
				type: "POST",
				dataType: "html",
				url: appUrl + "/?_route=autoload/server",
				success: function (msg) {
					$("#server").html(msg);
				}
			});
			$("#server").change(function () {
				var server = $("#server").val();
				$.ajax({
					type: "POST",
					dataType: "html",
					url: appUrl + "/?_route=autoload/plan",
					data: "jenis=VPN&server=" + server,
					success: function (msg) {
						$("#plan").html(msg);
					}
				});
			});
		}
	});
});


function showPrivacy() {
	$('#HTMLModal_title').html('Privacy Policy');
	$('#HTMLModal_konten').html('<center><img src="ui/ui/images/loading.gif"></center>');
	$('#HTMLModal').modal({
		'show': true,
		'backdrop': false,
	});
	$.get('pages/Privacy_Policy.html?' + (new Date()), function (data) {
		$('#HTMLModal_konten').html(data);
	});
}

function showTaC() {
	$('#HTMLModal_title').html('Terms and Conditions');
	$('#HTMLModal_konten').html('<center><img src="ui/ui/images/loading.gif"></center>');
	$('#HTMLModal').modal({
		'show': true,
		'backdrop': false,
	});
	$.get('pages/Terms_and_Conditions.html?' + (new Date()), function (data) {
		$('#HTMLModal_konten').html(data);
		$('#HTMLModal').modal('handleUpdate')
	});
}

// ============================================================================
// Mobile Sidebar Navigation Auto-Close Behavior
// ============================================================================
(function() {
    'use strict';
    
    // Only apply mobile sidebar behavior on mobile devices
    function isMobileDevice() {
        return window.innerWidth <= 767;
    }
    
    // Close sidebar function
    function closeSidebar() {
        if ($('body').hasClass('sidebar-open')) {
            $('body').removeClass('sidebar-open');
        }
    }
    
    // Auto-close sidebar after navigation link click on mobile
    function initSidebarAutoClose() {
        if (!isMobileDevice()) {
            return;
        }
        
        // Close sidebar when clicking on non-treeview menu items
        $('.sidebar-menu li > a:not(.treeview-toggle)').on('click', function(e) {
            // Check if this is a navigation link (not a treeview toggle)
            var $link = $(this);
            var $parent = $link.parent();
            
            // If it's not a treeview parent, close the sidebar
            if (!$parent.hasClass('treeview')) {
                setTimeout(closeSidebar, 150);
            }
        });
        
        // Close sidebar when clicking on submenu items
        $('.sidebar-menu .treeview-menu > li > a').on('click', function(e) {
            setTimeout(closeSidebar, 150);
        });
    }
    
    // Close sidebar when clicking overlay
    function initOverlayClickHandler() {
        if (!isMobileDevice()) {
            return;
        }
        
        $(document).on('click', function(e) {
            // Check if sidebar is open
            if (!$('body').hasClass('sidebar-open')) {
                return;
            }
            
            // Check if click is outside sidebar and sidebar toggle button
            var $target = $(e.target);
            var isClickInsideSidebar = $target.closest('.main-sidebar').length > 0;
            var isClickOnToggle = $target.closest('.sidebar-toggle').length > 0;
            
            if (!isClickInsideSidebar && !isClickOnToggle) {
                closeSidebar();
            }
        });
    }
    
    // Manage sidebar state on orientation change and window resize
    function initResizeHandler() {
        var resizeTimer;
        
        $(window).on('resize orientationchange', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                // Close sidebar if window is resized to desktop size
                if (window.innerWidth > 767 && $('body').hasClass('sidebar-open')) {
                    closeSidebar();
                }
            }, 250);
        });
    }
    
    // Add close button to sidebar on mobile
    function addSidebarCloseButton() {
        if (!isMobileDevice()) {
            return;
        }
        
        // Check if close button already exists
        if ($('.main-sidebar .sidebar-close-btn').length > 0) {
            return;
        }
        
        // Create close button
        var $closeBtn = $('<button>', {
            'class': 'sidebar-close-btn',
            'aria-label': 'Close menu',
            'html': '<i class="fa fa-times"></i>'
        });
        
        // Add click handler
        $closeBtn.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeSidebar();
        });
        
        // Prepend to sidebar (before menu)
        $('.main-sidebar').prepend($closeBtn);
    }
    
    // Initialize all mobile sidebar behaviors
    function initMobileSidebar() {
        addSidebarCloseButton();
        initSidebarAutoClose();
        initOverlayClickHandler();
        initResizeHandler();
    }
    
    // Initialize when document is ready
    $(document).ready(function() {
        initMobileSidebar();
    });
    
})();
