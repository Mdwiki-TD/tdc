<?php

namespace Actions\Html;
/*
Usage:
use function Actions\Html\banner_alert;
use function Actions\Html\add_quotes;
use function Actions\Html\login_card;
use function Actions\Html\makeCard;
use function Actions\Html\makeColSm4;
use function Actions\Html\makeDropdown;
use function Actions\Html\make_cat_url;
use function Actions\Html\make_col_sm_body;
use function Actions\Html\make_datalist_options;
use function Actions\Html\make_drop;
use function Actions\Html\make_form_check_input;
use function Actions\Html\make_input_group;
use function Actions\Html\make_input_group_no_col;
use function Actions\Html\make_mail_icon;
use function Actions\Html\make_mdwiki_title;
use function Actions\Html\make_mdwiki_user_url;
use function Actions\Html\make_modal_fade;
use function Actions\Html\make_project_to_user;
use function Actions\Html\make_talk_url;
use function Actions\Html\make_target_url;
use function Actions\Html\make_translation_url;
*/

include_once __DIR__ . '/html_side1.php';
//---
function add_quotes($str)
{
    $quote = preg_match("/[']+/u", $str) ? '"' : "'";
    return $quote . $str . $quote;
};
//---
function banner_alert($text)
{
    return <<<HTML
	<div class='container'>
		<div class="alert alert-danger d-flex align-items-center" role="alert">
			<svg xmlns="http://www.w3.org/2000/svg" class="d-none">
				<symbol id="check-circle-fill" viewBox="0 0 16 16">
					<path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
				</symbol>
				<symbol id="info-fill" viewBox="0 0 16 16">
					<path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z" />
				</symbol>
				<symbol id="exclamation-triangle-fill" viewBox="0 0 16 16">
					<path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
				</symbol>
			</svg>
			<svg class="bi flex-shrink-0 me-2" role="img" aria-label="Danger:">
				<use xlink:href="#exclamation-triangle-fill" />
			</svg>
			<div>
				$text
			</div>
		</div>
	</div>
	HTML;
}
function login_card()
{
    return <<<HTML
    <div class='card' style='font-weight: bold;'>
        <div class='card-body'>
            <div class='row'>
                <div class='col-md-10'>
                    <a role='button' class='btn btn-outline-primary' onclick='login()'>
                        <i class='fas fa-sign-in-alt fa-sm fa-fw mr-1'></i><span class='navtitles'>Login</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    HTML;
}

function make_modal_fade($label, $text, $id, $button = '')
{
    $exampleModalLabel = rand(1000, 9999);
    return <<<HTML

        <!-- Logout Modal-->
        <div class="modal fade" id="$id" tabindex="-1" role="dialog" aria-labelledby="$exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="$exampleModalLabel">$label</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">$text</div>
                    <div class="modal-footer">
                        $button
                        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    HTML;
}

function make_form_check_input($label, $name, $value_yes, $value_no, $checked)
{
    //---
    $label_line = (!empty($label)) ? "<label class='form-check-label' for='$name'>$label</label>" : "";
    //---
    return <<<HTML
        <div class='form-check form-switch'>
            $label_line
            <input class='form-check-input' type='checkbox' name='$name' value='$value_yes' $checked>
        </div>
    HTML;
}
//---
function make_mail_icon($tab)
{
    //---
    $mail_params = array(
        'user'   => $tab['user'],
        'lang'   => $tab['lang'],
        'target' => $tab['target'],
        'date'   => $tab['pupdate'],
        'title'  => $tab['title'],
        'nonav'  => '1'
    );
    //---
    $mail_url = "index.php?ty=Emails/msg&" . http_build_query($mail_params);
    //---
    $onclick = 'pupwindow("' . $mail_url . '")';
    //---
    return <<<HTML
    	<a class='btn btn-outline-primary btn-sm' onclick='$onclick'>Email</a>
    HTML;
}
//---
function make_project_to_user($projects, $project)
{
    //---
    $str = "<option value='Uncategorized'>Uncategorized</option>";
    // $str = "";
    //---
    foreach ($projects as $p_title => $p_id) {
        $cdcdc = $project == $p_title ? "selected" : "";
        $str .= <<<HTML
			<option value='$p_title' $cdcdc>$p_title</option>
		HTML;
    };
    //---
    return $str;
};
//---
function make_input_group($label, $id, $value, $required)
{
    $val2 = add_quotes($value);
    return <<<HTML
    <div class='col-md-3'>
        <div class='input-group mb-3'>
            <span class='input-group-text'>$label</span>
            <input class='form-control' type='text' name='$id' value=$val2 $required/>
        </div>
    </div>
    HTML;
};
//---
function make_input_group_no_col($label, $id, $value, $required)
{
    $val2 = add_quotes($value);
    return <<<HTML
    <div class='input-group mb-3'>
        <span class='input-group-text'>$label</span>
        <input class='form-control' type='text' name='$id' value=$val2 $required/>
    </div>
    HTML;
};
//---
function makeDropdown($tab, $cat, $id, $add)
{
    //---
    $options = "";
    //---
    foreach ($tab as $dd) {
        //---
        $se = '';
        //---
        if ($cat == $dd) $se = 'selected';
        //---
        $options .= <<<HTML
            <option value='$dd' $se>$dd</option>
        HTML;
        //---
    };
    //---
    $sel_line = "";
    //---
    if (!empty($add)) {
        $add2 = ($add == 'all') ? 'All' : $add;
        $sel = "";
        if ($cat == $add) $sel = "celected";
        $sel_line = "<option value='$add' $sel>$add2</option>";
    }
    //---
    return <<<HTML
        <select dir="ltr" id="$id" name="$id" class="form-select" data-bs-theme="auto">
            $sel_line
            $options
        </select>
    HTML;
};
//---
function makeCard($title, $table)
{
    return <<<HTML
    <div class="card">
        <div class="card-header aligncenter" style="font-weight:bold;">
            $title
        </div>
        <div class="card-body1 card2">
            $table
        </div>
        <!-- <div class="card-footer"></div> -->
    </div>
    HTML;
};

function makeColSm4($title, $table, $numb = 4, $table2 = '', $title2 = '')
{
    return <<<HTML
    <div class="col-md-$numb">
        <div class="card card2 mb-3">
            <div class="card-header">
                <span class="card-title" style="font-weight:bold;">
                    $title
                </span>
                <div style='float: right'>
                    $title2
                </div>
                <div class="card-tools">
                    <button type="button" class="btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body1 card2">
                $table
            </div>
            <!-- <div class="card-footer"></div> -->
        </div>
        $table2
    </div>
    HTML;
};

function make_col_sm_body($title, $subtitle, $table, $numb = 4)
{
    return <<<HTML
    <div class="col-md-$numb">
        <div class="card">
            <div class="card-header aligncenter1">
                <span style="font-weight:bold;">$title</span> $subtitle
            </div>
            <div class="card-body card2">
                $table
            </div>
        </div>
        <br>
    </div>
    HTML;
};
//---
function make_drop($uxutable, $code)
{
    $options  =  "";
    //---
    foreach ($uxutable as $name => $cod) {
        $cdcdc = $code == $cod ? "selected" : "";
        $options .= <<<HTML
		<option value='$cod' $cdcdc>$name</option>

		HTML;
    };
    //---
    return $options;
};
//---
function make_datalist_options($hyh)
{
    $options = '';
    foreach ($hyh as $language => $code) {
        $options .= "<option value='$code'>$language</option>";
    }
    return $options;
}
//---
function make_mdwiki_title($title)
{
    if (!empty($title)) {
        $encoded_title = rawurlencode(str_replace(' ', '_', $title));
        return "<a target='_blank' href='https://mdwiki.org/wiki/$encoded_title'>$title</a>";
    }
    return $title;
}
//---
function make_cat_url($category)
{
    if (!empty($category)) {
        $encoded_category = rawurlencode(str_replace(' ', '_', $category));
        return "<a target='_blank' href='https://mdwiki.org/wiki/Category:$encoded_category'>$category</a>";
    }
    return $category;
}
//---
function make_talk_url($lang, $user)
{
    return "<a target='_blank' href='//$lang.wikipedia.org/w/index.php?title=User_talk:$user'>talk</a>";
}
//---
function make_translation_url($title, $lang, $tr_type)
{
    //---
    $page = $tr_type == 'all' ? "User:Mr. Ibrahem/$title/full" : "User:Mr. Ibrahem/$title";
    //---
    $params = array(
        'page' => $page,
        'from' => "simple",
        'sx' => 'true',
        'to' => $lang,
        'targettitle' => $title
    );
    //---
    $url = "//$lang.wikipedia.org/wiki/Special:ContentTranslation";
    //---
    // $url .= "?" . http_build_query($params) . "#/sx/sentence-selector";
    $url .= "?" . http_build_query($params) . "#/sx?previousRoute=dashboard&eventSource=direct_preselect";
    //---
    // $url = "//$lang.wikipedia.org/wiki/Special:ContentTranslation?page=User%3AMr.+Ibrahem%2F$title&from=en&to=$lang&targettitle=$title#draft";
    //---
    return $url;
}
//---
function make_mdwiki_user_url($user)
{
    if (!empty($user)) {
        $encoded_user = rawurlencode(str_replace(' ', '_', $user));
        return "<a href='https://mdwiki.org/wiki/User:$encoded_user'>$user</a>";
    }
    return $user;
}
//---
function make_target_url($target, $lang, $name = '', $deleted = false)
{
    $display_name = (!empty($name)) ? $name : $target;
    if (!empty($target)) {
        $encoded_target = rawurlencode(str_replace(' ', '_', $target));
        $link = "<a target='_blank' href='https://$lang.wikipedia.org/wiki/$encoded_target'>$display_name</a>";

        if ($deleted == 1) {
            $link .= ' <span class="text-danger">(DELETED)</span>';
        }
        return $link;
    }
    return $target;
}
//---