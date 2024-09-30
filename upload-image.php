<?php
// in laravel you shoul make route with get like this Route::post('/upload_image', [ShowController:: class, 'UploadImage']); and use this function for upload image also set these 2 params in setting imageUploadURL: '/upload_image', imageUploadParam: 'file',
  
public function UploadImage(Request $request)
	{			
		// Define allowed extensions
		$allowedExts = array("gif", "jpeg", "jpg", "png");

		// Validate the incoming request for file upload
		$request->validate([
			'file' => 'required|image|mimes:jpeg,jpg,png,gif|max:2048', // Example validation
		]);

		// Get the uploaded file
		$file = $request->file('file');

		// Get the extension of the uploaded file
		$extension = $file->getClientOriginalExtension();

		// Check if the file is an allowed type
		if (in_array($extension, $allowedExts)) {
			// Generate a new random name for the file
			$name = sha1(microtime()) . "." . $extension;

			// Define the file path in your R2 bucket
			$filePath_R2 = 'ezead-com/public/froala-editorimages/' . $name;
			$filePath2_R2 = 'froala-editorimages/' . $name; // Modify this as needed for your directory structure
			

			// Store the image on R2 disk
			Storage::disk('r2')->put($filePath_R2, file_get_contents($file->getRealPath()));
			
			// Generate the public URL to access the stored image
			$r2BaseUrl = env('CLOUDFLARE_R2_URL');  // Fetch the base URL from the .env file
			$url = $r2BaseUrl . '/' . $filePath2_R2;	
			
			$response = new \stdClass();
			$response->link = $url;

			// Return the response as JSON
			return response()->json($response);
		} else {
			// Handle invalid file type
			return response()->json(['error' => 'Invalid file type.'], 400);
		}
	}