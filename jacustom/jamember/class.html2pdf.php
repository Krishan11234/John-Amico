<?php
class html2pdf
{
	var $html2ps_url = "http://devbox.mvisolutions.com/html2ps/demo/html2ps.php";
	var $screen_width = "1492";
	var $landscape = false;
	var $output;
    var $mpdf_instance;

    function __construct($root_url, $root_path) {
        $this->html2ps_url = $root_url . '/html2ps/demo/html2ps.php';
        $this->mdf_library_path = $root_path . '/mpdf/mpdf.php';
    }

	function set_html2ps_url($url)
	{
		$this->html2ps_url = $url;

		return;
	}

	function set_screen_width($width)
	{
		$this->screen_width = $width;

		return;
	}

	function set_landscape($switch)
	{
		$this->landscape = $switch;

		return;
	}

	function render_url($url)
	{
		$this->get_data($url);

		return;
	}

	function render_html($html, $url)
	{
		//$this->get_data("[HTML:" . $url . "]" . $html);
        $this->get_pdf_data($html);

		return;
	}

    function get_pdf_data($html) {
        require_once( $this->mdf_library_path );

        $mpdf = new mPDF();

        $mpdf->SetDisplayMode('fullpage');
        $mpdf->WriteHTML($html);

        $this->mpdf_instance = $mpdf;

        //$this->output = $mpdf->Output('', 'S');
        //$this->mpdf_instance->Output('result.pdf','D');
    }

	function get_data($path) {
		$settings = array(
			"process_mode"		=> "single",
			"URL"				=> $path,
			"pixels"			=> $this->screen_width,
			"scalepoints"		=> "1",
			"renderimages"		=> "1",
			"renderlinks"		=> "1",
			"renderfields"		=> "1",
			"media"				=> "Letter",
			"cssmedia"			=> "Screen",
			"leftmargin"		=> "0",
			"rightmargin"		=> "0",
			"topmargin"			=> "0",
			"bottommargin"		=> "0",
			"smartpagebreak"	=> "1",
			"method"			=> "fpdf",
			"pdfversion"		=> "1.3",
			"output"			=> "0",
			"convert"			=> "Convert File"
		);

		if($this->landscape === true) {
			$settings['landscape'] = 1;
		}

		$data = "";

		foreach($settings AS $setting => $value)
		{
			$data .= $setting . "=" . urlencode($value) . "&";
		}

		$data = rtrim($data, "&");

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $this->html2ps_url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		$this->output = curl_exec($ch);

		curl_close($ch);

		return;
	}

	function get_output()
	{
		if($this->output != "")
		{
			return $this->output;
		}
		else
		{
			return false;
		}
	}

	function output_pdf($file_name) {
		if($this->output != "")
		{
			if(!empty($this->mpdf_instance))
            {
                $this->mpdf_instance->Output($file_name,'D');
            }
            else {
                header("Content-type: application/pdf");
                header("Content-Disposition: attachment; filename=\"" . $file_name . "\"");

                print $this->output;
            }

			exit;
		}
		else
		{
			return false;
		}
	}

	function save_to_file($file_path)
	{
		if($this->output != "")
		{
			$fp = @fopen($file_path, "w");

			if($fp === false)
			{
				return false;
			}

			if(fwrite($fp, $this->output) === false)
			{
				return false;
			}

			fclose($fp);

			return true;
		}
		else
		{
			return false;
		}
	}
}

