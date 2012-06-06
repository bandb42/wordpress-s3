<?php
class TanTanWordPressS3PluginPublic {
    var $options;
    var $s3;
	var $meta;

	function TanTanWordPressS3PluginPublic() {
		$this->options = array();
		if (file_exists(dirname(__FILE__).'/config.php')) {
			require_once(dirname(__FILE__).'/config.php');
			if ($TanTanWordPressS3Config) $this->options = $TanTanWordPressS3Config;
		}
		add_action('plugins_loaded', array(&$this, 'addhooks'));
	}
    function addhooks() {
		add_filter('wp_get_attachment_url', array(&$this, 'wp_get_attachment_url'), 9, 2);
	}
	function wp_get_attachment_url($url, $postID) {
        if (!$this->options) $this->options = get_option('tantan_wordpress_s3');

        if ($this->options['wp-uploads'] && ($amazon = get_post_meta($postID, 'amazonS3_info', true))) {
            $accessDomain = (isset($this->options['virtual-host']) and $this->options['virtual-host']) ? $amazon['bucket'] : $amazon['bucket'].'.s3.amazonaws.com';
            return 'http://'.$accessDomain.'/'.$amazon['keys'][0];
        } else if ($this->options['bucket']) {
            // we'll just go ahead and update the attachment
            // so it's registered with Tan Tan correctly

            $filePath = parse_url($url, PHP_URL_PATH);
            $key = substr($filePath, 1);

            $amazon = array(
                'bucket' => $this->options['bucket'],
                'key' => $key,
            );
            update_post_meta($postID, 'amazonS3_info', $amazon);

            $accessDomain = (isset($this->options['virtual-host']) and $this->options['virtual-host']) ? $amazon['bucket'] : $amazon['bucket'].'.s3.amazonaws.com';
            return 'http://'.$accessDomain.'/'.$amazon['key'];

            /*
            // attachment is not uploaded to S3, fall back to local version
            return $url;
            */
        } else {
            // plugin not configured
            return $url;
        }
    }
}
?>