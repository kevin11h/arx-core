<?php
/**
 * bootstrap helper class
 *
 * @project : arx-contrib
 * @author  : Daniel Sum <daniel@cherrypulp.com>, Stéphan Zych <stephan@cherrypulp.com>
 */

namespace Arx\helpers;

use Arx\classes\Helper;
use Arx\classes\Strings;
use HTML, Form;

class Bootstrap extends Helper
{
    /**
     * Carousel helper => generate a carousel helpers
     * @todo child attributes handler
     *
     * @param       $data
     * @param array $params
     * @param null  $formatContent
     *
     * @return string
     */
    public static function carousel($data, $params = array(), $formatContent = null) {
        $defaults = array(
            'parent@' => array(
                'id' => 'carousel',
                'class' => 'carousel slide',
                'data-interval' => 'false',
                'data-wrap' => 'true',
            ),
            'child@' => array(
                'class' => 'item'
            ),
            'item_per_slide' => 1,
            'icon_prev' => 'icon-prev',
            'icon_next' => 'icon-next',
        );

        $params = array_merge_recursive($defaults, $params);

        if (is_array($params['parent@']['id'])) {
            $params['parent@']['id'] = end($params['parent@']['id']);
        }

        if (is_array($params['item_per_slide'])) {
            $params['item_per_slide'] = end($params['item_per_slide']);
        }

        $pagination = '<li data-target="#'.$params['parent@']['id'].'" data-slide-to="0" class="active"></li>';
        $slides = '<div class="'.$params['child@']['class'].' active">';

        $hideNav = ($params['item_per_slide'] >= count($data) ? true : false);

        if (!is_array($data)) {
            return;
        }

        $i = 0;
        foreach ($data as $key => $post) {
            if ($i > 0 && $i % $params['item_per_slide'] == 0) {
                $pagination .= '<li data-target="#'.$params['parent@']['id'].'" data-slide-to="'.($i / intval($params['item_per_slide'])).'"></li>';
                $slides .= '</div><div '.HTML::attributes($params['child@']).'>';
            }

            if (is_null($formatContent)) {
                $slides .= '<div class="'.HTML::attributes($params['child@']).'">'.$post.'</div>';
            } else {
                $slides .= $formatContent($post, $params, $i);
            }

            $i++;
        }

        $slides .= '</div>';

        if ($hideNav) {
            $output = '<div '.HTML::attributes($params['parent@']).'><div class="carousel-inner">'.$slides.'</div></div><!--/ #'.$params['parent@']['id'].' -->';
        } else {
            $output = '<div '.HTML::attributes($params['parent@']).'>
                <ol class="carousel-indicators">'.$pagination.'</ol>

                <div class="carousel-inner">'.$slides.'</div>

                <a class="left carousel-control" href="#'.$params['parent@']['id'].'" data-slide="prev"><span class="'.$params['icon_prev'].'"></span></a>
                <a class="right carousel-control" href="#'.$params['parent@']['id'].'" data-slide="next"><span class="'.$params['icon_next'].'"></span></a>
            </div><!--/ #'.$params['parent@']['id'].' -->';
        }

        return $output;
    } // carousel


    public static function columns($data, $params = array(), $formatContent = null) {
        $defaults = array(
            'parent@' => array(
                'class' => 'row',
            ),
            'child@' => array(
                'class' => 'col-sm-'
            ),
            'size' => 12
        );

        if (!is_array($data)) {
            return;
        }

        $params = array_merge_recursive($defaults, $params);

        if (is_array($params['parent@']['class'])) {
            $params['parent@']['class'] = end($params['parent@']['class']);
        }

        if (is_array($params['child@']['class'])) {
            $params['child@']['class'] = end($params['child@']['class']);
        }

        $output = '';

        $columns = count($data);
        $size = round($params['size'] / $columns);

        $i = 0;
        foreach ($data as $key => $column) {
            if ($i > 0 && $i % $columns === 0) {
                $output .= '</div><!--/ #'.$params['parent@']['class'].' --><div '.HTML::attributes($params['parent@']).'>';
            }

            if (is_null($formatContent)) {
                $params['child@']['class'] = $params['child@']['class'].$size;
                $output .= '<div '.HTML::attributes($params['child@']).'>'.$column.'</div>';
            } else {
                $output .= $formatContent($column, $params, $size);
            }

            $i++;
        }

        return '<div '.HTML::attributes($params['parent@']).'>'.$output.'</div>';
    } // columns


    /**
     * Navbar helper => generate a navbar helpers
     * @todo better navigation handler
     */
    public static function nav($data, $params = array())
    {
        $defParams = array(
            'active' => null,
            'parent' => 'ul',
            'child' => 'li',
            'parent@' => array('class' => 'nav'),
            'child@' => array(),
            'link@' => array()
        );

        $params = array_merge($defParams, $params);

        $html = '<' . $params['parent'] . ' ' . HTML::attributes($params['parent@']) . '>';

        foreach ($data as $key => $value) {
            $html .= '<' . $params['child'] . ' ' . HTML::attributes($params['child@']) . '>';
            $html .= '<a href="' . $value['link'] . '"' . HTML::attributes($params['link@']) . '>' . $value['name'] . '</a>';
            $html .= '</' . $params['child'] . '>';
        }

        $html .= '</' . $params['parent'] . '>';

        return $html;
    }

    public static function table($data, $params = array())
    {
        $defParams = array(
            'ajax' => false,
            'tfoot' => false,
            'table@' => array('class' => 'table'),
            'thead@' => array(),
            'tbody@' => array(),
            'tfoot@' => array()
        );

        $params = array_merge_recursive($defParams, $params);


        if (is_array($data)) {

            $html = '<table ' . HTML::attributes($params['table@']) . '><thead ' . HTML::attributes($params['thead@']) . '><tr>';
            foreach (reset($data) as $key => $name) {
                $html .= '<th>' . $key . '</th>';
            }
            $html .= '</tr></thead><tbody>';

            if ($params['ajax'] != true) {

                foreach ($data as $key => $row) {

                    $html .= '<tr>';
                    foreach ($row as $key => $col) {
                        $html .= '<td>' . $col . '</td>';
                    }
                    $html .= '</tr>';
                }
            } else {
                $html .= '<!--AJAXCALL-->';
            }
            $html .= '</tbody>';

            $html .= '</table>';

            return $html;
        }
    } // table

    /**
     * @param       $data
     * @param array $params
     * @param null  $formatContent
     */
    public static function tabs($data, $params = array(), $formatContent = null) {
        $defaults = array(
            'nav' => array(
                'parent@' => array(),
                'child@' => array(
                    'data-toggle' => 'tab'
                ),
            ),
            'content' => array(
                'parent@' => array(
                    'class' => 'tab-content'
                ),
                'child@' => array(
                    'class' => 'tab-pane fade'
                ),
            ),
        );

        $params = array_merge_recursive($defaults, $params);

        $output = '';

        if (is_null($formatContent)) {
            $nav = '<ul '.HTML::attributes($params['nav']['parent@']).'>';
            $content = '<div '.HTML::attributes($params['content']['parent@']).'>';

            foreach ($data as $key => $tab) {

                // nav
                $nav .= '<li'.($key === 0 ? ' class="active"' : '').'>'
                    .'<a '.HTML::attributes($params['nav']['child@']).' href="#tab-'.$key.'">'.$tab['title'].'</a>'
                .'</li>';


                // tabs
                $contentAttr = $params['content']['child@'];

                if ($key === 0) {
                    $contentAttr['class'] .= ' in active';
                }

                $content .= '<div '.HTML::attributes($contentAttr).' id="tab-'.$key.'">'
                    .$tab['content']
                .'</div>';

            }

            $nav .= '</ul>';
            $content .= '</div>';

            $output .= $nav.$content;
        } else {
            $output .= $formatContent($data, $tab, $key);
        }

        return $output;
    } // tabs

    /**
     * Breadcrumb helper for bootstrap
     *
     * @param       $data
     * @param array $params
     *
     * @return string
     */
    public static function breadcrumb($data, $params = array())
    {
        $defParams = array(
            'parent' => 'ul',
            'child' => 'li',
            'divider' => '<span class="divider">></span>',
            'parent@' => array('class' => 'breadcrumb'),
            'child@' => array()
        );

        $params = array_merge_recursive($defParams, $params);

        $msg = '<' . $params['parent'] . ' ' . HTML::attributes($params['parent@']) . '>';

        foreach ($data as $key => $value) {
            $attr = $params['li'];
            if ($key == 0 || $value['first']) {
                $params['child@']['class'] = $params['child@']['class'] . ' first';
            }
            if ($key == (count($data) - 1) || $value['last']) {
                $params['child@']['class'] = $params['child@']['class'] . ' last';
            }

            $msg .= '<' . $params['child'] . ' ' . HTML::attributes($params['child@']) . '><a href="' . $value['link'] . '">' . $value['name'] . '</a>' . $params['divider'] . '</' . $params['child'] . '>';
        }
        $msg .= '</' . $params['parent'] . '>';

        return $msg;
    }

    /**
     * Form helper for Bootstrap
     *
     * @return string
     */
    public static function formGroup(){

        $aParams = func_get_args();
        $label = $aParams[0];
        $type = $aParams[1];
        $name = $aParams[2];
        unset($aParams[0], $aParams[1]);

        $html = '<div class="form-group" id="form-' . $name . '">';

        $html .= Form::label($name, $label);

        $html .= call_user_func_array(array('Form', $type), $aParams);

        $html .= '</div>';

        return $html;
    }
}