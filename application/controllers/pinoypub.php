<?php

/**
 * Description of what this class does
 *
 * @author
 * @property CI_Config $config
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 * @property CI_Email $email
 * @property CI_Encrypt $encrypt
 * @property CI_Form_validation $form_validation
 * @property CI_FTP $ftp
 * @property CI_Input $input
 * @property CI_Loader $load
 * @property CI_Parser $parser
 * @property CI_Session $session
 * @property CI_Table $table
 * @property CI_URI $uri
 * @property Paypal_lib $paypal_lib
 * @property Template $template
 * @property Auth $auth
 * @property Pinoypub_model $pinoypub_model
 */
class Pinoypub extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('pinoypub_model');
	}

	function get_json_home_activity($encrypted_device_id, $device_model = '0', $recently_read = '')
	{
		// Create a new user if the user is new
		$customer_query = $this->db->get_where('customers', array('device_id' => $encrypted_device_id));

		if ($customer_query->num_rows == 0)
		{
			// Create a new customer
			$customer_data = array(
				'device_id' => $encrypted_device_id,
				'time_created' => now(),
				'model' => $device_model
			);

			$this->db->insert('customers', $customer_data);
		}

		// Let's get the featured books
		$data['featured_books'] = $this->create_books_from_result($this->pinoypub_model->get_featured_books());

		$data['latest_books'] = $this->create_books_from_result($this->pinoypub_model->get_latest_books(5));

		$data['recently_read_books'] = array();
		// Let's get recently read books
		if ($recently_read != '')
		{
			$data['recently_read_books'] = $this->create_books_from_result($this->pinoypub_model->get_recently_read_books($recently_read));
		}

		// Get the subscription status
		$subscription_status = $this->is_subscribed($encrypted_device_id);
		$seconds_to_expiration = 0;
		if ($subscription_status)
		{
			// Check how many seconds left til subscription expires
			$seconds_to_expiration = $this->subscription_expiration($encrypted_device_id);
		}

		$data['subscription_status'] = array(
			'status' => $subscription_status,
			'seconds_to_expiration' => $seconds_to_expiration
		);

		header('Content-type: application/json');
		echo json_encode($data);
	}

	/**
	 * Returns JSON of a single book given a book ID
	 *
	 * @param type $book_id
	 */
	function json_get_book($book_id)
	{
		$book = $this->pinoypub_model->get_book($book_id);

		header('Content-type: application/json');

		echo json_encode($this->create_book_from_row($book));
	}

	function json_get_featured_books()
	{
		$books = $this->pinoypub_model->get_featured_books();

		header('Content-type: application/json');

		echo json_encode($this->create_books_from_result($books));
	}

	function json_get_latest_books($quantity)
	{
		$books = $this->pinoypub_model->get_latest_books($quantity);

		header('Content-type: application/json');

		echo json_encode($this->create_books_from_result($books));
	}

	/**
	 * @param $device_id
	 * @return JSON
	 */
	function get_subscription_status($device_id)
	{
		$subscription_status = $this->is_subscribed($device_id);
		$seconds_to_expiration = 0;
		if ($subscription_status)
		{
			// Check how many seconds left til subscription expires
			$seconds_to_expiration = $this->subscription_expiration($device_id);
		}

		$data = array(
			'subscription_status' => $subscription_status,
			'seconds_to_expiration' => $seconds_to_expiration
		);

		header('Content-type: application/json');

		echo json_encode($data);
	}

	function insert_customer_purchase($device_id, $subscription_type, $payment_method)
	{
		// Get the customer_id first
		$customer_query = $this->db->get_where('customers', array('device_id' => $device_id));
		$customer = $customer_query->row();

		$data = array(
			'customer_id' => $customer->id,
			'time_purchased' => now(),
			'time_purchase_end' => 0,
			'payment_method' => $payment_method
		);

		switch ($subscription_type)
		{
			case "1-WEEK":
				$data['time_purchased_end'] = $data['time_purchased'] + 604800;
				break;
			case "3-MONTHS":
				$data['time_purchased_end'] = $data['time_purchased'] + 604800;
		}

		if ($data['time_purchased_end'] > 0)
		{
			$this->db->insert('customer_purchases', $data);
		}

		$subscription_status = $this->is_subscribed($device_id);
		$seconds_to_expiration = 0;
		if ($subscription_status)
		{
			// Check how many seconds left til subscription expires
			$seconds_to_expiration = $this->subscription_expiration($device_id);
		}

		$data = array(
			'subscription_status' => $subscription_status,
			'seconds_to_expiration' => $seconds_to_expiration
		);

		header('Content-type: application/json');

		echo json_encode($data);
	}

	/**
	 * Creates a clean array of a given book object
	 *
	 * @param Object $book_row
	 * @return array
	 */
	private function create_book_from_row($book_row)
	{
		$book_data = array(
			'book_id' => $book_row->book_id,
			'title' => $book_row->title,
			'summary' => $book_row->summary,
			'author' => $book_row->author_name,
			'publisher' => $book_row->publisher_name,
			'isbn' => $book_row->isbn,
			'coverImage' => $book_row->cover_image,
			'book_url' => $book_row->book_url,
			'page_count' => $book_row->page_count,
			'book_cover_url' => $book_row->book_cover_url
		);

		return $book_data;
	}

	function get_book_page($book_id, $page, $device_id)
	{
		// Check first if the device ID is eligible to read
		if ($this->is_subscribed($device_id))
		{
			// Get the book details
			$book_query = $this->db->get_where('books', array('id' => $book_id));
			$book = $book_query->row();

			if ($page == 0)
			{
				// Give the cover page
				$url = $book->book_cover_url;
			}
			else
			{
				if ($page < 10)
				{
					$page = '00' . $page;
				}
				else if ($page > 9 && $page < 100)
				{
					$page = '0' . $page;
				}
				$url = $book->book_url . $page . '.swf';
			}

			// Let's determine the content
			if ($page == 0)
			{
				// Then its an image
				$content = '<img src="' . $url . '" />';
			}
			else
			{
				$content = '
                <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="100%" height="100%" id="movie_name" align="middle">
                    <param name="movie" value="' . $url . '"/>
                    <!--[if !IE]>-->
                    <object type="application/x-shockwave-flash" data="' . $url . '" width="100%" height="100%">
                        <param name="movie" value="' . $url . '"/>
                        <!--<![endif]-->
                        <a href="https://market.android.com/details?id=com.adobe.flashplayer">
                            <img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player"/>
                        </a>
                        <!--[if !IE]>-->
                    </object>
                    <!--<![endif]-->
                </object>';
			}

			// Now let's display the url
			echo "
            <html>
                <head>
                    <title>$book->title</title>
                </head>
                <body>
                    $content
                </body>
            </html>";
		}
		else
		{
			echo 'Not subscribed';
		}
	}

	/**
	 * Creates a clean set of associative array containing a book array
	 *
	 * @param array $book_result
	 * @return array
	 */
	private function create_books_from_result($book_result)
	{
		$book_array = array();

		$i = 0;
		foreach ($book_result as $b)
		{
			$book_array['book' . $i] = $this->create_book_from_row($b);
			$i++;
		}

		return $book_array;
	}

	/**
	 * Checks if a device ID is subscribed or not
	 *
	 * @param $device_id
	 * @return bool
	 */
	private function is_subscribed($device_id)
	{
		$time_now = now();

		$user_query = $this->db->select('*')
			->join('customer_purchases', 'customers.id = customer_purchases.customer_id', 'left')
			->get_where('customers', array('device_id' => $device_id));

		$is_subscribed = FALSE;
		foreach ($user_query->result() as $u)
		{
			if ($u->time_purchase_end > $time_now)
			{
				$is_subscribed = TRUE;
			}
		}

		return $is_subscribed;
	}

	/**
	 * Gives the number of seconds til subscription expires.
	 * If the returned value is negative, it gives how many
	 * seconds a subscription has already expired
	 *
	 * @param $device_id
	 * @return int
	 */
	private function subscription_expiration($device_id)
	{
		$time_now = now();

		$user_query = $this->db->select('*')
			->join('customer_purchases', 'customers.id = customer_purchases.customer_id', 'left')
			->get_where('customers', array('device_id' => $device_id));

		// Get maximum time purchase end
		$time_purchase_end = 0;
		foreach ($user_query->result() as $u)
		{
			if ($u->time_purchase_end > $time_purchase_end)
			{
				$time_purchase_end = $u->time_purchase_end;
			}
		}
		return $time_purchase_end - $time_now;
	}

	function pasabayad($device_id, $subscription_type)
	{
		$denom = 0;
		if ($subscription_type == '1-WEEK')
		{
			$denom = 20;
		}
		elseif ($subscription_type == '3-MONTHS')
		{
			$denom = 210;
		}

		$merchant_id = 1200005;
		$merchant_password = 'Jdhgf94576wN';

		// Let's get the customer id
		$customer = $this->db->get_where('customers', array('device_id' => $device_id));
		$c = $customer->row();
		$customer_id = $c->id;

		// Let's create a new row in pasabayad_transactions
		$pasabayad_trans_data = array(
			'customer_id' => $customer_id,
			'amount' => $denom
		);
		$this->db->insert('pasabayad_transactions', $pasabayad_trans_data);

		$sequence = $this->db->insert_id();

		// Now let's get the session and update the row
		$session = md5($merchant_password . '174.120.168.226' . $merchant_id . $sequence);

		$this->db->where('id', $sequence);
		$this->db->update('pasabayad_transactions', array('session' => $session));

		// Pasabayad POST data
		$data = array(
			'merchant' => $merchant_id,
			'sequence' => $sequence,
			'session' => $session,
			'denom' => $denom,
			'success_url' => urlencode('http://pinoypub.bautista.me/pinoypub/pasabayad_success'),
			'error_url' => urlencode('http://pinoypub.bautista.me/pinoypub/pasabayad_error')
		);

		$data_string = '';
		//url-ify the data for the POST
		foreach ($data as $key => $value)
		{
			$data_string .= $key . '=' . $value . '&';
		}
		rtrim('&', $data_string);

		//echo $data_string . '<br />';
		//open connection
		$ch = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, 'https://pasabayad.com/authenticate/');
		curl_setopt($ch, CURLOPT_POST, count($data));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);

		//execute post
		$result = curl_exec($ch);

		if ($result === FALSE)
		{
			echo 'Curl error: ' . curl_error($ch);
		}
		else
		{
			$result_data = json_decode($result);

			if ($result_data->status == 'OK')
			{
				redirect('https://pasabayad.com/transact/?transact=' . $result_data->token . '&session=' . $session);
			}
			else
			{
				echo $result_data->message;
			}
		}

		//close connection
		curl_close($ch);
	}

	function pasabayad_success()
	{
		parse_str(substr(strrchr($_SERVER['REQUEST_URI'], "?"), 1), $_GET);

		if (isset($_GET['status']))
		{
			echo $_GET['status'];
		}
		else
		{
			// Get POST variables from Pasabayad
			$pasabayad_transaction_id = $this->input->post('sequence');

			$data = array(
				'status' => $this->input->post('trans_status'),
				'transaction_id' => $this->input->post('trans_id')
			);

			$this->db->where('id', $pasabayad_transaction_id);
			$this->db->update('pasabayad_transactions', $data);
			redirect('pinoypub/pasabayad_success/?status=' . $this->input->post('trans_status'));
		}
	}

	function activate()
	{
		$device_id = $this->input->post('device_id');
		$time = $this->input->post('time_now');
		$token = $this->input->post('token');
		$subscription_duration = $this->input->post('subscription_duration');
		$json_string = $this->input->post('json_string');
		$amount = $this->input->post('amount');

		$transaction_data = json_decode($json_string);

		$purchase_query = $this->db->get_where('customer_purchases', array('token' => $token));

		if ($purchase_query->num_rows() > 0)
		{
			$data['status'] = 'error';
			$data['message'] = 'Token has been used';
		}
		else
		{
			// Validate the token
			if ($token == md5($this->config->item('pinoypub_secret_salt') . $time . $device_id))
			{
				$time_now = now();

				$customer_query = $this->db->get_where('customers', array('device_id' => $device_id));
				$customer = $customer_query->row();

				$new_time_purchase_end = $time_now + $subscription_duration;

				// Let's also get the last purchase that the customer made
				$purchase_query = $this->db->order_by('time_purchase_end', 'desc')
					->limit(1)
					->get_where('customer_purchases', array('customer_id' => $customer->id));

				if ($purchase_query->num_rows() > 0)
				{
					$purchase = $purchase_query->row();
					if ($purchase->time_purchase_end > $time_now)
					{
						$new_time_purchase_end = $purchase->time_purchase_end + $subscription_duration;
					}
				}

				$purchase_data['customer_id'] = $customer->id;
				$purchase_data['time_purchased'] = $time_now;
				$purchase_data['time_purchase_end'] = $new_time_purchase_end;
				$purchase_data['time_device'] = $time;
				$purchase_data['token'] = $token;
				$purchase_data['amount_paid'] = $amount;

				$this->db->insert('customer_purchases', $purchase_data);

				$customer_purchase_id = $this->db->insert_id();

				if ($customer_purchase_id)
				{
					// Create a new wac_transactions row
					// Test first
					$wac_transaction['amount'] = $this->input->post('amount');
					$wac_transaction['total_amount_charged'] = $this->input->post('total_amount_charged');
					$wac_transaction['currency'] = $this->input->post('currency');
					$wac_transaction['status'] = $this->input->post('status');
					$wac_transaction['reference_code'] = $this->input->post('reference_code');
					$wac_transaction['server_reference_code'] = $this->input->post('server_reference_code');

					$wac_transaction['json_string'] = $json_string;
					$this->db->insert('wac_transactions', $wac_transaction);
					
					$wac_transaction_id = $this->db->insert_id();

					$this->db->where('id')
						->update('customer_purchases', array('wac_transaction_id' => $wac_transaction_id));

					$data['status'] = 'ok';
					$data['message'] = 'Subscription successfully activated';
				}
				else
				{
					$data['status'] = 'error';
					$data['message'] = 'Customer purchase failed to insert.';
				}
			}
			else
			{
				$data['status'] = 'error';
				$data['message'] = 'Token is invalid';
			}
		}

		header("Content-Type: application/json");
		echo json_encode($data);
	}

	function activate_ipn()
	{
		$this->load->library('paypal_lib');
		if ($this->paypal_lib->validate_ipn())
		{
			$vars = explode('|', $this->paypal_lib->ipn_data['custom']);
			$device_id = $vars[0];
			$time = $vars[1];
			$token = $vars[2];
			$subscription_duration = $vars[3];

			$purchase_query = $this->db->get_where('customer_purchases', array('token' => $token));

			if ($purchase_query->num_rows() > 0)
			{
				$data['status'] = 'error';
				$data['message'] = 'Token has been used';
			}
			else
			{
				// Validate the token
				if ($token == md5($this->config->item('pinoypub_secret_salt') . $time . $device_id))
				{
					$time_now = now();

					$customer_query = $this->db->get_where('customers', array('device_id' => $device_id));
					$customer = $customer_query->row();

					$new_time_purchase_end = $time_now + $subscription_duration;

					// Let's also get the last purchase that the customer made
					$purchase_query = $this->db->order_by('time_purchase_end', 'desc')
						->limit(1)
						->get_where('customer_purchases', array('customer_id' => $customer->id));

					if ($purchase_query->num_rows() > 0)
					{
						$purchase = $purchase_query->row();
						if ($purchase->time_purchase_end > $time_now)
						{
							$new_time_purchase_end = $purchase->time_purchase_end + $subscription_duration;
						}
					}

					$purchase_data['customer_id'] = $customer->id;
					$purchase_data['time_purchased'] = $time_now;
					$purchase_data['time_purchase_end'] = $new_time_purchase_end;
					$purchase_data['time_device'] = $time;
					$purchase_data['token'] = $token;

					$this->db->insert('customer_purchases', $purchase_data);

					$data['status'] = 'ok';
					$data['message'] = 'Subscription successfully activated';
				}
				else
				{
					$data['status'] = 'error';
					$data['message'] = 'Token is invalid';
				}
			}

			header("Content-Type: application/json");
			echo json_encode($data);
		}
	}

	function book($book_view_name, $device_id)
	{
		if ($this->is_subscribed($device_id))
		{
			$this->load->view('books/' . $book_view_name);
		}
		else
		{
			echo "Please subscribe first";
		}
	}

	function test()
	{
		$this->load->view('books/main_layout');
	}

}

/* End of file pinoypub.php */
/* Location: ./application/controllers/pinoypub.php */