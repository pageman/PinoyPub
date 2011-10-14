<?php

class Pinoypub_model extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}

	function get_book($id)
	{
		$query = $this->db->select('*,
			books.id as book_id,
			authors.name as author_name,
			publishers.name as publisher_name')
			->join('publishers', 'publishers.id = books.publisher_id', 'left')
			->join('authors', 'authors.id = books.author_id', 'left')
			->get_where('books', array('books.id' => $id));

		return $query->row();
	}

	function get_featured_books()
	{
		$query = $this->db->select('*,
			books.id as book_id,
			authors.name as author_name,
			publishers.name as publisher_name')
			->join('publishers', 'publishers.id = books.publisher_id', 'left')
			->join('authors', 'authors.id = books.author_id', 'left')
			->get_where('books', array('featured' => 1));

		return $query->result();
	}

	function get_latest_books($quantity)
	{
		$query = $this->db->select('*,
			books.id as book_id,
			authors.name as author_name,
			publishers.name as publisher_name')
			->join('publishers', 'publishers.id = books.publisher_id', 'left')
			->join('authors', 'authors.id = books.author_id', 'left')
			->order_by('books.id', 'desc')
			->limit($quantity)
			->get('books');

		return $query->result();
	}
	
	function get_recently_read_books($recently_read)
	{
		$book_ids = explode('-', $recently_read);
		
		$this->db->where_in('books.id', $book_ids);
		$query = $this->db->select('*,
			books.id as book_id,
			authors.name as author_name,
			publishers.name as publisher_name')
			->join('publishers', 'publishers.id = books.publisher_id', 'left')
			->join('authors', 'authors.id = books.author_id', 'left')
			->get('books');
		
		return $query->result();
	}
	
	function is_subscribed($device_id)
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

}

/* End of file pinoypub_model.php */
/* Location: ./application/models/pinoypub_model.php */