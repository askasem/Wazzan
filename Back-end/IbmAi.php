<?php 
class IbmAi {	
    // Model and API details
    private $sModel = "sdaia/allam-1-13b-instruct";
	private $projectID = "project_id";
	private $API_KEY = 'ibm_api_key'; // Allam API_KEY - AllamKey1
	private $ACCESS_TOKEN = null;
    private $token_expires_at = 0; // Timestamp when token expires (in seconds)
	
	private static $instance = null;

	// Private constructor to prevent multiple instances
    private function __construct() {}
	
	// Singleton pattern to ensure only one instance
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new IbmAi();
        }
        return self::$instance;
    }

	// Method to process the input question and get a response with Tashkeel
	public function shakkel($question){
		if(isset($question['content'])) {
			// First get a valid access token
			$accessToken = $this->getAccessToken();

			if ($accessToken == null) {
				return [
					'error' => true,
					'message' => 'An error occurred while getting Access Token'
				];
			}
			
			$headers  = [
				'Accept: application/json',
				'Content-Type: application/json',
				'Authorization: Bearer ' . $accessToken
			];

			// Input message for the AI model
			$inputMsg = "<s>[INST] \n<<SYS>>\nYou are an Arabic poetry linguistic assistant, specializing in adding Tashkeel (حركات) to given Arabic sentences (poem short parts).\nYour role is to only add Tashkeel (حركات) to the sentences you are given as INPUT, without adding any new word or punctuations.\nMake sure to use the same given INPUT, and add Tashkeel letters, i.e. (  َor  ً or  ِ or  ٍ or  ُ or  ٌ or  ّ or  ْ ), where appropriate (but at the same time to every letter as much as possible). \n\nSome examples are given below:\n\nInput: على قدر أهل العزم تأتي العزائم\nOutput: عَلَى قََدْرِ أَهْلِ العَزْمِ تَأتِي العَزَائِمُ\n\nInput: وتأتي على قدر الكرام المكارم\nOutput: وَتَأْتِي عَلَى قَدْرِ الكِرَامِ المَكَارِمُ\n\nInput: أمر على الديار ديار ليلى\nOutput: أَمُرُّ عَلَى الْدِّيَارِ دِيَارِ لَيْلَى\n\nInput: أنا الذي نظر الأعمى إلى أدبي\nOutput: أَنَا الَّذِيْ نَظَرَ الْأَعْمَى إِلَى أَدَبِي\n\n<</SYS>>\n\nAdd Tashkeel (حركات) to the \"INPUT\" Arabic sentence you will be given. \n[/INST]\n\n\nInput: ". $question["content"] . "\nOutput:" ;

            // Parameters for the AI model
            $parameters = [
				"decoding_method" => "greedy",
				"max_new_tokens" => 30,
				"min_new_tokens" => 0,
				"stop_sequences" => ["\n", "END"],
				"repetition_penalty" => 1
			];
           	$postData = [
				'input' => $inputMsg,
				'parameters' => $parameters,
				'model_id' => $this->sModel,
				'project_id' => $this->projectID
			];
			
			$ch = curl_init();			
			try {
				curl_setopt($ch, CURLOPT_URL, 'https://eu-de.ml.cloud.ibm.com/ml/v1/text/generation?version=2023-05-29');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
				
				$result = curl_exec($ch);
				// Check if CURL encountered an error
				if (curl_errno($ch)) {
					throw new Exception('CURL Error: ' . curl_error($ch));
				}

				// Check for HTTP status code errors (e.g., 4xx or 5xx responses)
				$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				if ($http_status !== 200) {
					throw new Exception('HTTP Error: ' . $http_status . '. Response: ' . $result);
				}
				
				return [
					'error' => false,
					'data' => json_decode($result, true) // assuming $result is JSON
				];
				
			} catch (Exception $e) {
				// Log the exception or error (optional)
				error_log('Error in getAnswer(): ' . $e->getMessage());
				return [
					'error' => true,
					'message' => 'An error occurred while processing getAnswer request'
				];
			} finally {
				if (is_resource($ch)) {
					curl_close($ch);
				}
			}
			
		} else {
			return [
				'error' => true,
				'message' => 'No input provided.'
			];
		}
	}

	// Method to process the input attempt and get a game reply
	public function game_reply($attempt){
		if(isset($attempt['content'])) {
			// First get a valid access token
			$accessToken = $this->getAccessToken();

			if ($accessToken == null) {
				return [
					'error' => true,
					'message' => 'An error occurred while getting Access Token'
				];
			}
			
			$headers  = [
				'Accept: application/json',
				'Content-Type: application/json',
				'Authorization: Bearer ' . $accessToken
			];

			// Input message for the AI model
			$inputMsg = "You are a helpful Arabic language assistant who is expert in Arabic poetry and its types (بحور الشعر). Your task is to generate a short sentence as a reply to congratulate, guide or encourage a user who is attempting to give poetry of a certain type as part of an educational game.\nYour response should be based on user'\''s last attempt for a specific poetry type, and being correct or not, and also utilize any previous attempts history (if given). \nFor poetry types you can only use one of these names:  (المتقارب, الهزج, الوافر, الطويل, الكامل, الرجز, المتدارك, البسيط, السريع, المنسرح, الرمل, الخفيف, المديد, المُجتث, المضارع, المقتضب).\nYou will be given as input:\n- اسم المستخدم (optional, don'\''t make up names for the reply if not given)\n- البحر المطلوب , and user'\''s answer being correct (صحيح) or wrong (خطأ)\n- المحاولات السابقة (could be empty).\n\nDo not invent new names of poetry types, or make assumptions that are not given in the input.\nDO NOT start output with an empty or new line, and keep it a short one-line sentence, no more than 10 words\n\nInput:\n"
			. $attempt["content"]
			. "\nOutput:";

            // Parameters for the AI model
            $parameters = [
				"decoding_method" => "greedy",
				"max_new_tokens" => 50,
				"min_new_tokens" => 5,
				"stop_sequences" => ["\n"],
				"repetition_penalty" => 1.5
			];
           	$postData = [
				'input' => $inputMsg,
				'parameters' => $parameters,
				'model_id' => $this->sModel,
				'project_id' => $this->projectID
			];
			
			$ch = curl_init();			
			try {
				curl_setopt($ch, CURLOPT_URL, 'https://eu-de.ml.cloud.ibm.com/ml/v1/text/generation?version=2023-05-29');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
				
				$result = curl_exec($ch);
				// Check if CURL encountered an error
				if (curl_errno($ch)) {
					throw new Exception('CURL Error: ' . curl_error($ch));
				}

				// Check for HTTP status code errors (e.g., 4xx or 5xx responses)
				$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				if ($http_status !== 200) {
					throw new Exception('HTTP Error: ' . $http_status . '. Response: ' . $result);
				}
				
				return [
					'error' => false,
					'data' => json_decode($result, true) // assuming $result is JSON
				];
				
			} catch (Exception $e) {
				// Log the exception or error (optional)
				error_log('Error in getAnswer(): ' . $e->getMessage());
				return [
					'error' => true,
					'message' => 'An error occurred while processing getAnswer request'
				];
			} finally {
				if (is_resource($ch)) {
					curl_close($ch);
				}
			}
			
		} else {
			return [
				'error' => true,
				'message' => 'No input provided.'
			];
		}
	}
	
	// Method to get a valid access token
	private function getAccessToken() {
        // Check if token is valid and not expired
        if ($this->ACCESS_TOKEN && time() < $this->token_expires_at) {
            return $this->ACCESS_TOKEN; // Return the existing token if still valid
        }

        // Token is expired or not available, so get a new one
        $url = "https://iam.cloud.ibm.com/identity/token";

        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
        ];

        $postData = http_build_query([
            'grant_type' => 'urn:ibm:params:oauth:grant-type:apikey',
            'apikey' => $this->API_KEY
        ]);

        $ch = curl_init();			
		try {
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

			$result = curl_exec($ch);

			if (curl_errno($ch)) {
				throw new Exception('CURL Error: ' . curl_error($ch));
			}

			curl_close($ch);

			$response = json_decode($result, true);

			// Check if token is retrieved successfully
			if (isset($response['access_token'])) {
				$this->ACCESS_TOKEN = $response['access_token'];
				$expires_in = isset($response['expires_in']) ? $response['expires_in'] : 3600; // Default to 3600 seconds if not provided
				$this->token_expires_at = time() + $expires_in; // Store expiration time
				return $this->ACCESS_TOKEN;
			} else {
				error_log('Failed to retrieve access token. Response: ' . json_encode($response));
				return null;
			}
		} catch (Exception $e) {
			// Log the exception or error (optional)
			error_log('Error in getAccessToken(): ' . $e->getMessage());
			return null;
		} finally {
			if (is_resource($ch)) {
				curl_close($ch);
			}
		}
    }
}
?>
