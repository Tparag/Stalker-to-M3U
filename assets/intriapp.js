const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

function load_dashboard_data() {
    $.ajax({
        "url": "api.php",
        "type": "POST",
        "data": "action=dashboard_data",
        "success": function (data) {
            try { data = JSON.parse(data); } catch (err) { }
            if (data.status == "success") {
                $("#mac_url").val(data.data.stalker_base.server_url);
                $("#mac_id").val(data.data.stalker_base.mac_id);
                $("#mac_serial").val(data.data.stalker_base.serial);
                $("#mac_dv1").val(data.data.stalker_base.device_id1);
                $("#mac_dv2").val(data.data.stalker_base.device_id2);
                $("#mac_sig").val(data.data.stalker_base.signature);
                $(".mac_tv_expiry").text(data.data.stalker_data.expiry);
                $(".mac_tv_count").text(data.data.stalker_data.channels_count);
                $(".mac_stream_proxy_status").text(data.data.settings.stream_proxy);
                $(".mac_logging_status").text(data.data.settings.logging_status);
                
                // Set toggle states
                $("#toggle_proxy").prop('checked', data.data.settings.stream_proxy === "ON");
                $("#toggle_logging_chk").prop('checked', data.data.settings.logging_status === "ON");
                $("#toggle_admin_button").prop('checked', data.data.settings.admin_button === "ON");
                $("#toggle_playback_cache").prop('checked', data.data.settings.playback_cache.status === "ON");
                $(".mac_admin_button_status").text(data.data.settings.admin_button);
                $(".mac_playback_cache_status").text(data.data.settings.playback_cache.status);
                $("#txt_playback_expiry").val(data.data.settings.playback_cache.expiry);
                
                if(data.data.settings.playback_cache.status === "ON") {
                    $("#playback_expiry_container").slideDown();
                } else {
                    $("#playback_expiry_container").slideUp();
                }

                if (data.data.stalker_base.server_url !== "" && data.data.stalker_base.server_url !== null && data.data.stalker_base.server_url !== undefined) {
                    $("#box_stalker_details").fadeIn();
                    $("#box_genre_filter").fadeIn();
                    $("#btn_delete_mac").fadeIn();
                    
                    // Store current filter globally for reference when rendering list
                    window.current_genre_filter = data.data.settings.genre_filter || [];
                    load_genres_for_filter();
                }
                $("#btn_toggle_proxy_status").fadeIn();
                $("#btn_toggle_logging").fadeIn();

            }
        },
        "error": function (data) {
            Toast.fire({ title: "Oops", text: "Server or Network Failed", icon: "error" });
        }
    });
}

$("#btn_nadminLogout").on("click", function () {
    logout_admin();
});

function logout_admin() {
    $.ajax({
        "url": "api.php",
        "type": "POST",
        "data": "action=logout",
        "success": function (data) {
            location.reload();
        },
        "error": function (data) {
            location.reload();
        }
    });
}
$("#txt_nadminPIN").keyup(function (event) {
    if (event.keyCode === 13) {
        changeAccessPIN();
    }
});
$("#btn_nadminPIN").on("click", function () {
    changeAccessPIN();
});
function changeAccessPIN() {
    $("#btn_nadminPIN").attr("disabled", "");
    let new_pin = $("#txt_nadminPIN").val();
    $.ajax({
        "url": "api.php",
        "type": "POST",
        "data": "action=change_access_pin&pin=" + new_pin,
        "success": function (data) {
            try { data = JSON.parse(data); } catch (err) { }
            if (data.status == "success") {
                logout_admin();
                Toast.fire({ title: "OK", text: data.message, icon: "success" });
            }
            else {
                if (data.status == "error") {
                    Toast.fire({ title: "Oops", text: data.message, icon: "warning" });
                }
                else {
                    Toast.fire({ title: "Oops", text: "Unknown Error Occured", icon: "error" });
                }
            }
            $("#btn_nadminPIN").removeAttr("disabled");
        },
        "error": function (data) {
            $("#btn_nadminPIN").removeAttr("disabled");
            Toast.fire({ title: "Oops", text: "Server or Network Failed", icon: "error" });
        }
    });
}
$("#btn_mac").on("click", function () {
    save_update_mac();
});
$("#mac_sig").keyup(function (event) {
    if (event.keyCode === 13) {
        save_update_mac();
    }
});
$("#mac_id").keyup(function (event) {
    if (event.keyCode === 13) {
        save_update_mac();
    }
});
$("#mac_url").keyup(function (event) {
    if (event.keyCode === 13) {
        save_update_mac();
    }
});
function save_update_mac() {
    let btnml = $("#btn_mac").html();
    $("#btn_mac").attr("disabled", "");
    $("#btn_mac").text("Please Wait ...");
    let payload = "server_url=" + $("#mac_url").val() + "&mac_id=" + $("#mac_id").val() + "&serial=" + $("#mac_serial").val() + "&device_id1=" + $("#mac_dv1").val() + "&device_id2=" + $("#mac_dv2").val() + "&signature=" + $("#mac_sig").val();
    $.ajax({
        "url": "api.php",
        "type": "POST",
        "data": "action=save_mac_portal&" + payload,
        "success": function (data) {
            try { data = JSON.parse(data); } catch (err) { }
            if (data.status == "success") {
                $("#btn_mac").removeAttr("disabled");
                $("#btn_mac").text(" Save ");
                load_dashboard_data();
                window.setTimeout(function () { update_mac_data(); }, 5000);
                Toast.fire({ title: "OK", text: data.message, icon: "success" });
            }
            else {
                if (data.status == "error") {
                    Toast.fire({ title: "Oops", text: data.message, icon: "warning" });
                }
                else {
                    Toast.fire({ title: "Oops", text: "Unknown Error Occured", icon: "error" });
                }
            }
            $("#btn_mac").removeAttr("disabled");
            $("#btn_mac").html(btnml);
        },
        "error": function (data) {
            $("#btn_mac").removeAttr("disabled");
            $("#btn_mac").html(btnml);
            Toast.fire({ title: "Oops", text: "Server or Network Failed", icon: "error" });
        }
    });
}
$("#btn_delete_mac").on("click", function () {
    confirm_mac_deletion();
});
function confirm_mac_deletion() {
    Swal.fire({
        title: "Do you really want to delete Stalker details?",
        showCancelButton: true,
        confirmButtonText: "Delete",
    }).then((result) => {
        if (result.isConfirmed) {
            delete_mac_data();
        }
    });
}
function delete_mac_data() {
    let btnml = $("#btn_delete_mac").html();
    $("#btn_delete_mac").attr("disabled", "");
    $("#btn_delete_mac").text("Please Wait ...");
    $.ajax({
        "url": "api.php",
        "type": "POST",
        "data": "action=delete_mac_portal",
        "success": function (data) {
            try { data = JSON.parse(data); } catch (err) { }
            if (data.status == "success") {
                location.reload();
                Toast.fire({ title: "OK", text: data.message, icon: "success" });
            }
            else {
                if (data.status == "error") {
                    Toast.fire({ title: "Oops", text: data.message, icon: "warning" });
                }
                else {
                    Toast.fire({ title: "Oops", text: "Unknown Error Occured", icon: "error" });
                }
            }
            $("#btn_delete_mac").removeAttr("disabled");
            $("#btn_delete_mac").html(btnml);
        },
        "error": function (data) {
            $("#btn_delete_mac").removeAttr("disabled");
            $("#btn_delete_mac").html(btnml);
            Toast.fire({ title: "Oops", text: "Server or Network Failed", icon: "error" });
        }
    });
}
function update_mac_data() {
    Toast.fire({ title: "Please Wait ...", text: "Fetching MAC Details !", icon: "info" });
    $.ajax({
        "url": "api.php",
        "type": "POST",
        "data": "action=update_mac_data",
        "success": function (data) {
            try { data = JSON.parse(data); } catch (err) { }
            if (data.status == "success") {
                load_dashboard_data();
                Toast.fire({ title: "OK", text: data.message, icon: "success" });
            }
            else {
                if (data.status == "error") {
                    Toast.fire({ title: "Oops", text: data.message, icon: "warning" });
                }
                else {
                    Toast.fire({ title: "Oops", text: "Unknown Error Occured", icon: "error" });
                }
            }
        },
        "error": function (data) {
            Toast.fire({ title: "Oops", text: "Server or Network Failed", icon: "error" });
        }
    });
}

// Toggles Logic
$("#toggle_proxy").on("change", function () {
    $.ajax({
        "url": "api.php",
        "type": "POST",
        "data": "action=toggle_stream_proxy",
        "success": function (data) {
            try { data = JSON.parse(data); } catch (err) { }
            if (data.status == "success") {
                Toast.fire({ title: "Updated", text: data.message, icon: "success" });
            }
            load_dashboard_data();
        }
    });
});

$("#toggle_logging_chk").on("change", function () {
    $.ajax({
        "url": "api.php",
        "type": "POST",
        "data": "action=toggle_logging",
        "success": function (data) {
            try { data = JSON.parse(data); } catch (err) { }
            if (data.status == "success") {
                Toast.fire({ title: "Updated", text: data.message, icon: "success" });
            }
            load_dashboard_data();
        }
    });
});

$("#toggle_admin_button").on("change", function () {
    $.ajax({
        "url": "api.php",
        "type": "POST",
        "data": "action=toggle_admin_button",
        "success": function (data) {
            try { data = JSON.parse(data); } catch (err) { }
            if (data.status == "success") {
                Toast.fire({ title: "Updated", text: data.message, icon: "success" });
            }
            load_dashboard_data();
        }
    });
});

$("#toggle_playback_cache").on("change", function () {
    $.ajax({
        "url": "api.php",
        "type": "POST",
        "data": "action=toggle_playback_cache",
        "success": function (data) {
            try { data = JSON.parse(data); } catch (err) { }
            if (data.status == "success") {
                Toast.fire({ title: "Updated", text: data.message, icon: "success" });
            }
            load_dashboard_data();
        }
    });
});

$("#btn_save_playback_expiry").on("click", function () {
    let expiry = $("#txt_playback_expiry").val();
    $("#btn_save_playback_expiry").attr("disabled", "");
    $.ajax({
        "url": "api.php",
        "type": "POST",
        "data": "action=update_playback_expiry&expiry=" + expiry,
        "success": function (data) {
            try { data = JSON.parse(data); } catch (err) { }
            if (data.status == "success") {
                Toast.fire({ title: "Saved", text: data.message, icon: "success" });
            }
            $("#btn_save_playback_expiry").removeAttr("disabled");
            load_dashboard_data();
        },
        "error": function () {
            $("#btn_save_playback_expiry").removeAttr("disabled");
            Toast.fire({ title: "Oops", text: "Network Error", icon: "error" });
        }
    });
});

$("#btn_toggle_proxy_status").on("click", function () {
    $("#btn_toggle_proxy_status").attr("disabled", "");
    $("#btn_toggle_proxy_status").html('<i class="fa-solid fa-arrows-rotate"></i>');
    $.ajax({
        "url": "api.php",
        "type": "POST",
        "data": "action=toggle_stream_proxy",
        "success": function (data) {
            try { data = JSON.parse(data); } catch (err) { }
            if (data.status == "success") {
                load_dashboard_data();
                Toast.fire({ title: "OK", text: data.message, icon: "success" });
            }
            else {
                if (data.status == "error") {
                    Toast.fire({ title: "Oops", text: data.message, icon: "warning" });
                }
                else {
                    Toast.fire({ title: "Oops", text: "Unknown Error Occured", icon: "error" });
                }
            }
            $("#btn_toggle_proxy_status").removeAttr("disabled");
            $("#btn_toggle_proxy_status").html('<i class="fa-solid fa-rotate-right"></i>');
        },
        "error": function (data) {
            $("#btn_toggle_proxy_status").removeAttr("disabled");
            $("#btn_toggle_proxy_status").html('<i class="fa-solid fa-rotate-right"></i>');
            Toast.fire({ title: "Oops", text: "Server or Network Failed", icon: "error" });
        }
    });
});

$("#btn_toggle_logging").on("click", function () {
    $("#btn_toggle_logging").attr("disabled", "");
    $("#btn_toggle_logging").html('<i class="fa-solid fa-arrows-rotate"></i>');
    $.ajax({
        "url": "api.php",
        "type": "POST",
        "data": "action=toggle_logging",
        "success": function (data) {
            try { data = JSON.parse(data); } catch (err) { }
            if (data.status == "success") {
                load_dashboard_data();
                Toast.fire({ title: "OK", text: data.message, icon: "success" });
            }
            else {
                if (data.status == "error") {
                    Toast.fire({ title: "Oops", text: data.message, icon: "warning" });
                }
                else {
                    Toast.fire({ title: "Oops", text: "Unknown Error Occured", icon: "error" });
                }
            }
            $("#btn_toggle_logging").removeAttr("disabled");
            $("#btn_toggle_logging").html('<i class="fa-solid fa-rotate-right"></i>');
        },
        "error": function (data) {
            $("#btn_toggle_logging").removeAttr("disabled");
            $("#btn_toggle_logging").html('<i class="fa-solid fa-rotate-right"></i>');
            Toast.fire({ title: "Oops", text: "Server or Network Failed", icon: "error" });
        }
    });
});

$("#btn_clear_logs").on("click", function () {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to clear all application logs?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, clear it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'api.php',
                type: 'POST',
                data: 'action=clear_logs',
                success: function (response) {
                    try { response = JSON.parse(response); } catch (err) { }
                    Toast.fire(
                        'Cleared!',
                        response.message,
                        'success'
                    );
                }
            });
        }
    });
});

// Genre Filter Logic
function load_genres_for_filter() {
    $.ajax({
        url: 'api.php',
        type: 'POST',
        data: { action: 'get_genres' },
        success: function(response) {
            try { if(typeof response === 'string') response = JSON.parse(response); } catch(e){}
            if(response && response.status === 'success' && response.data) {
                render_genre_list(response.data.list);
            } else {
                $("#genre_list_items").html('<div class="text-center p-3 text-danger">Failed to load genres.</div>');
            }
        },
        error: function() {
            $("#genre_list_items").html('<div class="text-center p-3 text-danger">Network error while loading genres.</div>');
        }
    });
}

function render_genre_list(genres) {
    if(!genres) {
        $("#genre_list_items").html('<div class="text-center p-3 text-muted">No genres available.</div>');
        return;
    }
    let html = '';
    const filter = window.current_genre_filter || [];
    
    // Sort genres by name
    const sortedKeys = Object.keys(genres).sort((a, b) => {
        if(!genres[a] || !genres[b]) return 0;
        return genres[a].localeCompare(genres[b]);
    });
    
    sortedKeys.forEach(id => {
        const title = genres[id];
        if(title.toLowerCase() === 'all' || id.toLowerCase() === 'all') return;
        
        const isChecked = filter.includes(id) ? 'checked' : '';
        html += `
            <div class="col">
                <div class="genre-item" data-title="${title.toLowerCase()}">
                    <input type="checkbox" id="gen_${id}" value="${id}" ${isChecked}>
                    <label for="gen_${id}">${title}</label>
                </div>
            </div>
        `;
    });
    
    if(html === '') {
        html = '<div class="text-center p-3 text-muted">No genres found.</div>';
    }
    
    $("#genre_list_items").html(html);
}

// Search Genres
$("#genre_search").on("input", function() {
    const term = $(this).val().toLowerCase();
    $(".genre-item").each(function() {
        const title = $(this).data("title");
        if(title.includes(term)) {
            $(this).closest(".col").show();
        } else {
            $(this).closest(".col").hide();
        }
    });
});

// Tick All
$("#btn_genre_all").on("click", function() {
    $(".genre-item:visible input[type='checkbox']").prop("checked", true);
});

// Untick All
$("#btn_genre_none").on("click", function() {
    $(".genre-item:visible input[type='checkbox']").prop("checked", false);
});

// Save Genre Filter
$("#btn_save_genre_filter").on("click", function() {
    const selected = [];
    $(".genre-item input[type='checkbox']:checked").each(function() {
        selected.push($(this).val());
    });
    
    $("#btn_save_genre_filter").attr("disabled", "").html('<i class="fa-solid fa-spinner fa-spin"></i> Saving...');
    
    $.ajax({
        url: 'api.php',
        type: 'POST',
        data: {
            action: 'save_genre_filter',
            filter: selected
        },
        success: function(response) {
            try { if(typeof response === 'string') response = JSON.parse(response); } catch(e){}
            if(response && response.status === 'success') {
                Toast.fire({ title: 'Success', text: response.message, icon: 'success' });
                window.current_genre_filter = selected;
            } else {
                let msg = (response && response.message) ? response.message : "Undefined Error";
                Toast.fire({ title: 'Error', text: msg, icon: 'error' });
            }
            $("#btn_save_genre_filter").removeAttr("disabled").html('<i class="fa-solid fa-save"></i> Save Genre Filter');
        },
        error: function() {
            Toast.fire({ title: 'Error', text: 'Network error', icon: 'error' });
            $("#btn_save_genre_filter").removeAttr("disabled").html('<i class="fa-solid fa-save"></i> Save Genre Filter');
        }
    });
});