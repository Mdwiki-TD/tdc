<?php

namespace Actions\Html;
/*
Usage:
use function Actions\Html\banner_alert;
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
use function Actions\Html\make_mail_icon_new;
use function Actions\Html\make_mdwiki_title;
use function Actions\Html\make_mdwiki_user_url;
use function Actions\Html\make_modal_fade;
use function Actions\Html\make_project_to_user;
use function Actions\Html\make_talk_url;
use function Actions\Html\make_target_url;
use function Actions\Html\make_translation_url;
use function Actions\Html\div_alert; //  div_alert($texts, $type)
*/
//---
use Tables\SqlTables\TablesSql;

function banner_alert($text)
{
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    return <<<HTML
	<div class='container'>
		<div class="alert alert-danger" role="alert">
			<i class="bi bi-exclamation-triangle"></i> $text
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
    $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
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
    $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    $label_line = (!empty($label)) ? "<label class='form-check-label' for='$name'>$label</label>" : "";
    //---
    return <<<HTML
        <div class='form-check form-switch'>
            $label_line
            <input class='form-check-input' type='checkbox' name='$name' value='$value_yes' $checked>
        </div>
    HTML;
}

function make_mail_icon_new($tab, $func_name = "")
{
    //---
    if (empty($func_name)) $func_name = "pup_window_new";
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
    return <<<HTML
    	<a class='btn btn-outline-primary btn-sm spannowrap' pup-target='$mail_url' onclick='$func_name(this)'>Email</a>
    HTML;
}

function make_project_to_user($project)
{
    //---
    $str = "<option value='Uncategorized'>Uncategorized</option>";
    // $str = "";
    //---
    foreach (TablesSql::$s_projects_title_to_id as $p_title => $p_id) {
        $p_title_escaped = htmlspecialchars($p_title, ENT_QUOTES, 'UTF-8');
        $cdcdc = $project == $p_title ? "selected" : "";
        $str .= <<<HTML
			<option value='$p_title' $cdcdc>$p_title_escaped</option>
		HTML;
    };
    //---
    return $str;
};
//---
function make_input_group($label, $id, $value, $required)
{
    $val2 = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    return <<<HTML
    <div class='col-md-3'>
        <div class='input-group mb-3'>
            <span class='input-group-text'>$label</span>
            <input class='form-control' type='text' name='$id' value='$val2' $required/>
        </div>
    </div>
    HTML;
};
//---
function make_input_group_no_col($label, $id, $value, $required)
{
    $val2 = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    return <<<HTML
    <div class='input-group mb-3'>
        <span class='input-group-text'>$label</span>
        <input class='form-control' type='text' name='$id' value='$val2' $required/>
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
        $dd_escaped = htmlspecialchars($dd, ENT_QUOTES, 'UTF-8');
        $se = ($cat == $dd) ? 'selected' : '';
        //---
        $options .= <<<HTML
            <option value='$dd' $se>$dd_escaped</option>
        HTML;
        //---
    };
    //---
    $sel_line = "";
    //---
    if (!empty($add)) {
        $add2 = ($add == 'all') ? 'All' : htmlspecialchars($add, ENT_QUOTES, 'UTF-8');
        $sel = "";
        if ($cat == $add) $sel = "selected";
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
    $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
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
    $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $title2 = htmlspecialchars($title2, ENT_QUOTES, 'UTF-8');
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
    $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $subtitle = htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8');
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
        $name_escaped = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $cdcdc = $code == $cod ? "selected" : "";
        $options .= <<<HTML
		<option value='$cod' $cdcdc>$name_escaped</option>

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
        $language_escaped = htmlspecialchars($language, ENT_QUOTES, 'UTF-8');
        $options .= "<option value='$code'>$language_escaped</option>";
    }
    return $options;
}

function make_mdwiki_title($title)
{
    if (!empty($title)) {
        $encoded_title = rawurlencode(str_replace(' ', '_', $title));
        $title_escaped = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        return "<a target='_blank' href='https://mdwiki.org/wiki/$encoded_title'>$title_escaped</a>";
    }
    return $title;
}

function make_cat_url($category)
{
    if (!empty($category)) {
        $encoded_category = rawurlencode(str_replace(' ', '_', $category));
        $category_escaped = htmlspecialchars($category, ENT_QUOTES, 'UTF-8');
        return "<a target='_blank' href='https://mdwiki.org/wiki/Category:$encoded_category'>$category_escaped</a>";
    }
    return $category;
}

function make_talk_url($lang, $user)
{
    return "<a target='_blank' href='//$lang.wikipedia.org/w/index.php?title=User_talk:$user'>talk</a>";
}

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

function make_mdwiki_user_url($user)
{
    if (!empty($user)) {
        $encoded_user = rawurlencode(str_replace(' ', '_', $user));
        $user_escaped = htmlspecialchars($user, ENT_QUOTES, 'UTF-8');
        return "<a href='https://mdwiki.org/wiki/User:$encoded_user'>$user_escaped</a>";
    }
    return $user;
}

function make_target_url($target, $lang, $name = '', $deleted = false)
{
    $display_name = (!empty($name)) ? $name : $target;
    if (!empty($target)) {
        $encoded_target = rawurlencode(str_replace(' ', '_', $target));
        $display_name_escaped = htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8');
        $link = "<a target='_blank' href='https://$lang.wikipedia.org/wiki/$encoded_target'>$display_name_escaped</a>";

        if ($deleted == 1) {
            $link .= ' <span class="text-danger">(DELETED)</span>';
        }
        return $link;
    }
    return $target;
}

function div_alert($texts, $type = "secondary")
{
    $div = "";
    // ---
    if (empty($type)) $type = "secondary";
    // ---
    if (!empty($texts)) {
        $div .= "<div class='container m-1'><div class='alert alert-$type' role='alert'>";
        foreach ($texts as $text) {
            $div .= htmlspecialchars($text) . "<br>";
        }
        $div .= "</div></div>";
    }
    // ---
    return $div;
}

function make_edit_icon_new($target, $edit_params, $text = "Edit")
{
    //---
    if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
        $edit_params['test'] = 1;
    }
    //---
    $edit_params['nonav'] = 1;
    //---
    $edit_url = "index.php?ty=$target&" . http_build_query($edit_params);
    //---
    if (empty($text)) $text = "Edit";
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    //---
    $class_sm = ($text == "Edit") ? "btn-sm" : "";
    //---
    return <<<HTML
		<a class='btn btn-outline-primary $class_sm' pup-target='$edit_url' onclick='pup_window_new(this)'>$text</a>
	HTML;
}
