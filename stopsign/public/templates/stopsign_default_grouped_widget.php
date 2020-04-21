<?php
/**
 * Grouped (table) output template for Stopsign (widget)
 *
 * @package    Stopsign
 * @subpackage Stopsign/public/templates
 * @author     Joaquim Homrighausen <joho@webbplatsen.se>
 *
 * This file is searched for in the following locations (in order):
 *
 * 1. /wp-content/themes/<theme>/stopsign/templates/stopsign_default_grouped_widget.php
 *    (e.g. /wp-content/themes/twentytwenty/stopsign/templates/stopsign_default_grouped_widget.php)
 *
 * 2. /wp-content/plugins/stopsign/public/templates/stopsign_default_grouped_widget.php
 *
 * If you want to make modifications that survive an upgrade, make sure that you
 * create the above stopsign directory in your template directory; it's always
 * a good idea to use a child theme, even if you're not actually modifying it.
 *
 * Modify the HTML to your liking. There are two variables, one is the table
 * header and one is each table row. You can see the variables that are
 * replaced. Do not modify the GROUPED_HEADER and GROUPED_ROW lines. The table
 * header is repeated for each group.
 *
 * The entire output is enclosed in a DIV with CSS class "stopsign-grouped-stop-widget"
 *
 *
 * stopsign_default_grouped_widget.php
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

// Grouped header, one for each number+direction
$stopsign_grouped_header = <<<GROUPED_HEADER
<div class="stopsign-grouped-header-widget">
<span>{stopsign_group_number}</span> &middot; {stopsign_group_direction}
</div>
GROUPED_HEADER;

//Grouped row, 1-nn for each number+direction
$stopsign_grouped_row = <<<GROUPED_ROW
<div class="stopsign-grouped-time-widget">{stopsign_group_time}</div>
GROUPED_ROW;
?>