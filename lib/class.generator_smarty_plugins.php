<?php

#-------------------------------------------------------------------------
# Module: ModuleGenerator for CMS Made Simple (@kuzmany)
#
#-------------------------------------------------------------------------
# CMS - CMS Made Simple is (c) 2005 by Ted Kulp (wishy@cmsmadesimple.org)
# This project's homepage is: http://www.cmsmadesimple.org
#
#-------------------------------------------------------------------------
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
# Or read it online: http://www.gnu.org/licenses/licenses.html#GPL
#
#-------------------------------------------------------------------------
/**
 * Description of class
 *
 * @author @albertcansado
 */

require __DIR__ . '/class.generator_stringtemplate.php';

class generator_smarty_plugins
{
	public static function _videoIframe($url = '', $params = array())
	{
		$html = '<iframe type="text/html" src="{{src}}" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen{{attr}}></iframe>';
		return str_replace(
			array('{{src}}', '{{attr}}'),
			array(
				$url,
				(new StringTemplate())->formatAttributes($params)
			),
			$html
		);
	}

	public static function _showYoutube($id, $params = array())
	{
		$url = '//www.youtube.com/embed/{{id}}?rel=0&html5=1&showinfo=0&autohide=1';
		return self::_videoIframe(
			str_replace('{{id}}', $id, $url),
			$params
		);
	}

	public static function _showVimeo($id, $params = array())
	{
		$url = '//player.vimeo.com/video/{{id}}?title=0&byline=0&portrait=0&badge=0&color=cc6f1a';
		return self::_videoIframe(
			str_replace('{{id}}', $id, $url),
			$params
		);
	}

	public static function video($params, &$smarty)
    {
    	// Params
    	$id = isset($params['v']['id']) ? $params['v']['id'] : false;
    	$type = isset($params['v']['type']) ? $params['v']['type'] : false;
    	$assign = isset($params['assign']) ? $params['assign'] : false;
    	unset($params['v'], $params['assign']);

    	if (!$id || !$type) {
    		return;
    	}

    	// Get Video Type html
    	$method = '_show' . $type;
    	if (method_exists(__CLASS__, $method)) {
    		$result = self::$method($id, $params);
    	} else {
    		$result = 'Video type ' . $type . ' not implemented';
    	}

    	if ($assign) {
    		$smarty->assign($assign, $result);
    		return;
    	}
    	return $result;

    }
}