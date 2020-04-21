(function( $ ) {
	'use strict';
	/**
     * stopsign-admin.js
     * Copyright (C) 2020 Joaquim Homrighausen <joho@webbplatsen.se>
     *
     * This file is part of Stopsign. Stopsign is free software.
     *
     * You may redistribute it and/or modify it under the terms of the
     * GNU General Public License version 2, as published by the Free Software
     * Foundation.
     *
     * Stopsign is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
     * See the GNU General Public License for more details.
     *
     * You should have received a copy of the GNU General Public License
     * along with the Stopsign package. If not, write to:
     *  The Free Software Foundation, Inc.,
     *  51 Franklin Street, Fifth Floor
     *  Boston, MA  02110-1301, USA.
	 */
    var ajax_url = plugin_ajax_object.ajax_url;
    var ajax_api_url = plugin_ajax_object.ajax_api_url;
    var ajax_api_key = plugin_ajax_object.ajax_api_key;
    var ajax_last_lookup = '';

    // Clunky, but we only have four strings ...
    var txt_commute_stop_id = plugin_ajax_object.txt_commute_stop_id;
    var txt_commute_stop_name = plugin_ajax_object.txt_commute_stop_name;
    var txt_commute_stop_types = plugin_ajax_object.txt_commute_stop_types;
    var txt_no_match = plugin_ajax_object.txt_no_match;

    $(function() {

        // Lookup commute stop ID
        $('#stopsignajax').keyup(function(){
            var searchText = $(this).val();
            if (searchText.length < 3) {
                return(true);
            }
            if (searchText === ajax_last_lookup) {
                console.log('Not searching, same as previous lookup');
                return(true);
            }
            // Don't lookup duplicates
            ajax_last_lookup = searchText;

            var commute_lookup_url = '';

            if (ajax_api_url && ajax_api_url.length > 0 && ajax_api_key && ajax_api_key.length > 0) {
                commute_lookup_url = ajax_api_url + encodeURIComponent (ajax_api_key) +
                                     '&format=json' +
                                     '&maxNo=10' +
                                     '&input=' + encodeURIComponent (searchText+'?');
            } else {
                return(true);
            }
            $.ajax({
                url: commute_lookup_url,
                cache: false,
                type: 'get',
                success: function(response){
                    var found_count = 0;
                    if (response.StopLocation) {
                        var html_out = '<table class="stopsign-result">' +
                                       '<tr class="row"><th>' + txt_commute_stop_id +
                                       '</th><th>' + txt_commute_stop_name +
                                       '</th><th>' + txt_commute_stop_types + '</th></tr>';
                        $.each(response.StopLocation,function(i,v){
                            if (v.products & 128) {
                                found_count++;
                                html_out = html_out +
                                           '<tr>' +
                                           '<td>' + v.id + '</td>' +
                                           '<td>' + v.name + '</td>' +
                                           '<td>(' + v.products + ')</td>' +
                                           '</tr>';
                            }
                        });
                        html_out = html_out + '</table>';
                        $('#stopsign_search_result').html( html_out );
                    }
                    if (found_count == 0) {
                        $('#stopsign_search_result').html( txt_no_match );
                    }
                }
            });
        });

    });

})( jQuery );
