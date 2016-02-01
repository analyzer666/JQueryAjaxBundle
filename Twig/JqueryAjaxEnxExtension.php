<?php
namespace SymBundle\JQueryAjaxEnxBundle\Twig;

/**
 *
 * @author Nickolai Nedovodiev <analyzer666@gmail.com>
 *
 */
class JqueryAjaxEnxExtension extends \Twig_Extension
{

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('ja_request', $this->remoteCall(), array(
                'is_safe' => array(
                    'html'
                )
            )),
            new \Twig_SimpleFunction('ja_button', $this->buttonTag(), array(
                'is_safe' => array(
                    'html'
                )
            )),
            new \Twig_SimpleFunction('ja_link', $this->linkTag(), array(
                'is_safe' => array(
                    'html'
                )
            ))
        );
    }

    /**
     * Generate Js function to send ajax request.
     *
     * @param array $options
     *   ajax:
     *    -$options['type']          : type: '...'; default - POST
     *    -$options['dataType']      : dataType: '$dataType'; default - html 
     *    +$options['url']           : url: "..."
     *    -$options['before']        : beforeSend: function(){"..."}
     *    +$options['update']        : success: function( data ){"...").html(data)
     *    -$options['after']         : adding to the end of success:
     *    -$options['complete']      : complete: function(){"..."}
     *    -$options['loading']       : div id to show and hide. '#loading_div_id'
     */
    public function remoteCall($options = array())
    {
        return function ($options) {
            $type = isset($options['type']) ? $options['type'] : "POST";
            $dataType = isset($options['dataType']) ? $options['dataType'] : "html";
            $js = "$.ajax({
							url: '" . $options['url'] . "',
							type: '" . $type . "',
							dataType: '" . $dataType . "',";
            
            if (isset($options['before']) || (isset($options['loading']))) {
                $js .= "beforeSend: function(){";
                if (isset($options['before'])) {
                    $before = str_replace('"', "'", $options['before']);
                    $js .= $before;
                }
                if (isset($options['loading'])) {
                    $js .= "$('#".$options['loading']."').show(); ";
                }
                $js .= "},";
            }

            if (isset($options['data'])) {
                $js .= "data: ".$options['data'].",";
            }
            
            $js .= "success: function( data ){ $('" . $options['update'] . "').html(data);";

            if (isset($options['after'])) {
                $after = str_replace('"', "'", $options['after']);
                $js .= $after;
            }
            
            if (isset($options['loading'])) {
                $js .= "$('#".$options['loading']."').hide(); ";
            }

            $js .= "}";
            
            if (isset($options['complete'])) {
                $complete = str_replace('"', "'", $options['complete']);
                $js .= ",complete: function(){" . $complete . "},";
            }
            
            $js .= "});";
            
            return $js;
        };
    }
    
     /**
     * Generate Js function to send ajax form data.
     *
     * @param array $options
     *    -$options['type']          : POST-GET
     *    -$options['dataType']      : html-...
     *    +$options['url']           : ajax url: "..."
     *    -$options['before']        : ajax beforeSend: function(){"..."}
     *    -$options['success']       : ajax success: function( data ){ "...").html(data)
     *    -$options['after']         : js скрипт добавляется в конец success функции
     */
    public function submitCall($options = array())
    {
    	return function ($options) {
    		$type = isset($options['type']) ? $options['type'] : "POST";
    		$dataType = isset($options['dataType']) ? $options['dataType'] : "html";
    		$js = "$.ajax({
		    		url: '" . $options['url'] . "',
		    		type: '" . $type . "',
		    		data: $(this.form.elements).serializeArray(),
		    		dataType: '" . $dataType . "',";
    		if (isset($options['before'])) {
    			$before = str_replace('"', "'", $options['before']);
    			$js .= "beforeSend: function(){" . $before . "},";
    		}
    		$js .= "success: function( data ){";
    		if (isset($options['success'])) {
    			$success = str_replace('"', "'", $options['success']);
    			$js .= $success ;
    		}
    		if (isset($options['after'])) {
    			$after = str_replace('"', "'", $options['after']);
    			$js .= $after;
    		}
    
    		$js .= "}});";
    
    		return $js;
    	};
    }

    /**
     * Generate link tag with js function to send ajax request
     *
     * @param array $options
     *   -$options['confirm']        : true-false:
     *   -$options['confirm_msg']    :
     *   -$options['class']          : <a class="..."
     *   -$options['id']             : <a id="..."
     *   -$options['href']           : <a href="..."
     *   +$options['text']           : <a>...</a>
     */
    public function linkTag($options = array())
    {
        $jsRequest = $this->remoteCall();
        
        return function ($options) use($jsRequest)
        {
            $confirm = '';
            if (isset($options['confirm']) && $options['confirm'] === true) {
                
                $msg = "Are you sure you want to perform this action?";
                if(isset($options['confirm_msg'])) {
                    $msg = htmlentities(str_replace("'", '"', $options['confirm_msg']), ENT_QUOTES);                  
                }
            	$confirm .= "if(confirm('".$msg."'))" ;
            }
            if (!isset($options['href'])) {
                $options['href'] = "#" ;
            }
            $html = '<a class="' . (isset($options['class']) ? $options['class'] : "") . '"'.
                     (isset($options['id']) ? ' id="'.$options['id'].'" ' : " ").'
    		 		 href="' . $options['href'] . '"
    				 onclick="' .$confirm. call_user_func($jsRequest, $options) . 'return false;">';
            $html .= $options['text'];
            $html .= '</a>';
            
            return $html;
        };
    }
    
    /**
     * Generate button tag with js function to send ajax request
     *
     * @param array $options
     *   -$options['confirm']        : true-false:
     *   -$options['confirm_msg']    : message to confirm request
     *   -$options['class']          : <button class="..."
     *   -$options['id']             : <button id="..."
     *   -$options['type']           : <button type="..."
     *   -$options['data-toggle']    : <button data-toggle="..."
     *   -$options['data-target']    : <button data-target="..."
     *   -$options['data-dismiss']   : <button data-dismiss="..."
     *   -$options['aria-label']     : <button aria-label="..."
     *   +$options['text']           : <button>...</button>
     */
    public function buttonTag($options = array())
    {
        $jsRequest = $this->remoteCall();
        
        return function ($options) use($jsRequest)
        {
            $confirm = '';
            if (isset($options['confirm']) && $options['confirm'] == true) {
                
                $msg = "Are you sure you want to perform this action?";
                if(isset($options['confirm_msg'])) {
                    $msg = htmlentities(str_replace("'", '"', $options['confirm_msg']), ENT_QUOTES);                  
                }
                $confirm .= "if(confirm('".$msg."'))" ;
            }
            $html = '<button class="' . (isset($options['class']) ? $options['class'] : "") . '"'.
                     (isset($options['id']) ? ' id="'.$options['id'].'" ' : " ").'
                     type="' . (isset($options['type']) ? $options['type'] : "submit") . '"' .
                     (isset($options['data-toggle']) ? ' data-toggle="'.$options['data-toggle'].'"' : " ").
                     (isset($options['data-target']) ? ' data-target="'.$options['data-target'].'"' : " ").
                     (isset($options['data-dismiss']) ? ' data-dismiss="'.$options['data-dismiss'].'"' : " ").
                     (isset($options['aria-label']) ? ' aria-label="'.$options['aria-label'].'"' : " ").'
                     onclick="' .$confirm. call_user_func($jsRequest, $options) . 'return false;">';
            $html .= $options['text'];
            $html .= '</button>';
            
            return $html;
        };
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'symbundle_jquery_ajax';
    }
}

