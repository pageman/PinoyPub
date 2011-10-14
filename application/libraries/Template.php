<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * TEMPLATE LIBRARY
 *
 * This library provides a flexible templating system for codeigniter.
 * Documentation and examples will soon follow.
 * @TODO: Provide examples and better documentation for template library
 *
 * @author Chris Bautista <chris@bautista.me>
 */
class Template {

	function __construct()
	{
		$this->CI = & get_instance();
	}

	function build($view, $data = '', $header = '', $footer = '', $template = '')
	{
		if ($template == '' || !is_dir('application/views/templates/' . $template))
		{
			$template = $this->CI->config->item('template_default');
		}

		$tpl['header'] = $this->CI->config->item('template_view_directory') . $template . '/header';
		$tpl['footer'] = $this->CI->config->item('template_view_directory') . $template . '/footer';

		if (!isset($header['title']))
		{
			$header['title'] = $this->CI->config->item('template_title_default');
		}

		if (!isset($header['js']))
		{
			$header['javascript'] = '';
		}
		else
		{
			$header['javascript'] = '';
			$jsArray = $header['js'];

			for ($i = 0; $i < count($jsArray); $i++)
			{
				$header['javascript'] .= $this->parse_js($jsArray[$i]);
			}
		}
		if (!isset($footer['js']))
		{
			$footer['javascript'] = '';
		}
		else
		{
			$footer['javascript'] = '';
			$jsArray = $footer['js'];
			for ($i = 0; $i < count($jsArray); $i++)
			{
				$footer['javascript'] .= $this->parse_js($jsArray[$i]);
			}
		}

		if (!isset($header['css']))
		{
			$header['css_string'] = '';
		}
		else
		{
			$header['css_string'] = '';
			$cssArray = $header['css'];
			for ($i = 0; $i < count($cssArray); $i++)
			{
				$header['css_string'] .= $this->parse_css($cssArray[$i]);
			}
		}

		if (!isset($header['breadcrumbs']))
		{
			$header['bc'] = FALSE;
		}
		else
		{
			$header['bc'] = '<div id="breadcrumbs"><ol>';
			$bc = $header['breadcrumbs'];
			for ($i = 0; $i < count($bc); $i++)
			{
				if ($i == 0) $header['bc'] .= '<li class="first">' . $bc[$i] . '</li>';
				elseif ($i == count($bc) - 1)
						$header['bc'] .= '<li class="last">' . $bc[$i] . '</li>';
				else $header['bc'] .= '<li>' . $bc[$i] . '</li>';;
			}
			$header['bc'] .= '</ol></div>';
		}

		$this->CI->load->view($tpl['header'], $header);
		$this->CI->load->view($view, $data);
		$this->CI->load->view($tpl['footer'], $footer);
	}

	function parse_js($src)
	{
		# If $src is a complete URL, don't include the base_js_url() function
		if (substr($src, 0, 4) == 'http')
		{
			$result = '<script type="text/javascript" src="' . $src . '"></script>
            ';
		}
		else
		{
			$result = '<script type="text/javascript" src="/assets/js/' . $src . '"></script>
            ';
		}
		return $result;
	}

	function parse_css($src)
	{
		# If $src is a complete URL, don't include the base_css_url() function
		if (substr($src, 0, 4) == 'http')
		{
			$result = '<link rel="stylesheet" href="' . $src . '" type="text/css" media="screen" />
            ';
		}
		else
		{
			$result = '<link rel="stylesheet" href="/assets/css/' . $src . '" type="text/css" media="screen" />
            ';
		}
		return $result;
	}

}

/* End of file Template.php */
/* Location: ./application/libraries/Template.php */