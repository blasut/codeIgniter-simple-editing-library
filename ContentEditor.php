<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class ContentEditor {
	
	// To be able to use the entire CI codebase from my custom library
	private $CI;

	// Which views the library should get and show for the user.
	private $viewsToEdit = array();
	
	// In case we need options, not really used right now.
	private $options = array();
	
	// This contains all the views that exists.
	private $allViews = array();
	
	// a temporary storage variable for the view in it's original form.
	private $oldView = "";
	
	// Specifies the directory, where the views are.
	// This is relative to the main "index.php" file. So if you are using CI pre 2.0
	// The path may look like:
	// 'system/application/views';
	private $dir;

	public function __construct($params = array()) {
		// This loads an instance of CI - so you can use all the libraries and helpers CI provides.       		
		$this->CI =& get_instance();
		// Loads the file helper.
		$this->CI->load->helper('file_helper');
		
		// Right now "params" is just which views to edit.
		// This sets the which views the library should show to the user.
		$this->viewsToEdit = $params['allowedViews'];	
		
		$this->dir = (isset($params['dir'])) ? $params['dir'] : "";
		
		// Loads in all the views.
		$this->allViews = $this->getAllViews();
	}
	
	public function overView($return = false, $view = "") {
		
		// I'm lazy. I don't want to type "$this->" all the time.
		$viewsToEdit = $this->viewsToEdit;
		$allViews = $this->allViews;
		
		// Loop through the views and check if there is an view with the name of the viewToEdit.
		foreach($allViews as $allView) {
			foreach($viewsToEdit as $viewToEdit) {				
				 if(strtolower($allView['fileName']) === strtolower($viewToEdit)) {
					$data['viewsToEdit'][] = $allView;
				 }
			}
		}
		
		
		if($return) {
			// Load the view, with the viewsToEdit
			$this->CI->load->view($view, $data);
		} else {
			// Just return the views, in cause you want use a templating system.
			return $data;
		}
	}
	
	public function saveView($options = array()) {
		
		// The file to write.
		$file = $this->dir . $options['viewName'];
		
		// Write the new contents to the file.
		$result = write_file($file, $options['newContent']);
		
		// There is no real error-checking.
		// It just silently fales if it can't open and write to the file.
		if($result) {
			redirect('admin/content');
		}
	}
	
	public function editView($viewToEdit, $return = false, $editView = "") {
		
		$newView = "";
		
		// Gets the viewToEdit.
		foreach($this->allViews as $views) {
			if($viewToEdit === strtolower($views['fileName'])) {
				$newView = $views['relFilePath'];
			}
		}
		
		// Loads in the old view, or the view to edit to "oldView".
		$this->oldView = $this->CI->load->view($newView, "",TRUE);
		
		$data['content'] = $this->oldView;
		$data['viewName'] = $newView;
		
		
		if($return) {
			// Sends the data to the edit view
			$this->CI->load->view($editView, $data);
		} else {
			// Just return the views, in cause you want use a templating system.
			return $data;
		}
	}
	
	
	private function getAllViews() {
		$dir =  'application/views/';
		
		// Gets all the files in views and all subdirs.
		$filesWithExt = get_filenames($dir, TRUE);
		
		// Remove the index.html from the files array
		unset($filesWithExt[0]);
		
		// make sure that all the values are corrected.
		$filesWithExt = array_values($filesWithExt);
		
		// All the files in one array.
		$size = count($filesWithExt);
		for($i = 0; $i < $size; $i++) {
		
			/* Get the relative path to each view inside the views folder */
			$viewsPos = strpos($filesWithExt[$i], "views");
			$viewsPos += 6;
			
			$fileRelPath = substr($filesWithExt[$i], $viewsPos);
		
			$osName = php_uname('s');
		
			/* To get the filename only. */
			if($osName === "Windows NT") {
				$explodedString = explode("\\", $filesWithExt[$i]);
			} else if ($osName === "Linux") {
				$explodedString = explode("/", $filesWithExt[$i]);
			}
			$fileName = ucfirst(substr(end($explodedString), 0, -4));
			
			$files[] = array(
				'filePath' => $filesWithExt[$i],
				'relFilePath' => $fileRelPath,
				'fileName' => $fileName
			);
		
		}
		
		return $files;
		
	}

}
