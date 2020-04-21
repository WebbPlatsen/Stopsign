<?php
/**
 * Default (plain table) output template for Stopsign
 *
 * @package    Stopsign
 * @subpackage Stopsign/public/templates
 * @author     Joaquim Homrighausen <joho@webbplatsen.se>
 *
 * This file is searched for in the following locations (in order):
 *
 * 1. /wp-content/themes/<theme>/stopsign/templates/stopsign_default.php
 *    (e.g. /wp-content/themes/twentytwenty/stopsign/templates/stopsign_default.php)
 *
 * 2. /wp-content/plugins/stopsign/public/templates/stopsign_default.php
 *
 * If you want to make modifications that survive an upgrade, make sure that you
 * create the above stopsign directory in your template directory; it's always
 * a good idea to use a child theme, even if you're not actually modifying it.
 *
 * Modify the HTML to your liking. There are two variables, one is the table
 * header and one is each table row. You can see the variables that are
 * replaced. Do not modify the TABLE_HEADER and TABLE_ROW lines.
 *
 * The entire table is enclosed in a DIV with CSS class "stopsign-stop"
 *
 *
 * stopsign_default.php
 * Copyright (C) 2020 Joaquim Homrighausen
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

// Table header
$stopsign_table_header = <<<TABLE_HEADER
<tr>
<th></th>
<th>{stopsign_hdr_type}</th>
<th>{stopsign_hdr_number}</th>
<th>{stopsign_hdr_time}</th>
<th>{stopsign_hdr_direction}</th>
</tr>
TABLE_HEADER;

// Table row, do not modify the first or the last line
$stopsign_table_row = <<<TABLE_ROW
<tr>
<td class="stopsign_default_type_icon">{stopsign_type_icon}</td>
<td class="stopsign_default_type">{stopsign_type}</td>
<td class="stopsign_default_number">{stopsign_number}</td>
<td class="stopsign_default_time">{stopsign_time}</td>
<td class="stopsign_default_direction">{stopsign_direction}</td>
</tr>
TABLE_ROW;
?>