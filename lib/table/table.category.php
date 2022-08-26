<?php
/**
 * Copyright C2009G
 * 
 * Các hàm quản lý loại sản phẩm.
 * @update 2021.04.24 21h00
 */

// Cấu hình hệ thống
include_once '../../configs.php';
// Thư viện cần thiết
include_once 'table.product.php';

/**
 * Thêm mới loại sản phẩm
 * 
 * @param array $data (Mảng kết hợp chứa dữ liệu gửi lên từ form)
 * 
 * @return void
 */
function categoryAdd($data)
{
		
			// Dữ liệu của loại sản phẩm mới
			$parent_id = (int)$data['parent_id'];
			$top = (isset($data['top']) ? (int)$data['top'] : 0);
			$column = (int)$data['column'];
			$sort_order = (int)$data['sort_order'];
			$status = (int)$data['status'];
			$featured = (int)$data['featured'];
			
			$name = db_escape($data['name']); 
			$description = db_escape($data['description']);
			
			// Sử dụng cú pháp INSERT INTO ... SET
			// để không phải viết các tên cột trên một hàng dài.
			$sql = " 
				INSERT INTO `category` 
				SET `parent_id` = {$parent_id}, 
					`top` = {$top}, 
				    `column` = {$column}, 
				    `sort_order` = {$sort_order}, 
				    `status` = {$status}, 
				    `featured` = {$featured}, 
				    `date_modified` = NOW(), 
				    `date_added` = NOW(),
				    `name` = '{$name}',
					`description` = '{$description}'
			";
			db_q($sql);
			
			// Lấy lại id của bản ghi vừa thêm mới
			$category_id = (int)db_lastId();
			
			// Cập nhật ảnh đại diện cho loại sản phẩm vừa thêm mới.
			if (isset($data['image']))
			{	
				$image = db_escape($data['image']);
				$sql = " 
					UPDATE `category` 
					SET `image` = '{$image}' 
					WHERE `category_id` = {$category_id}
				";
				db_q($sql);
			}
				
			// Thêm mới dữ liệu liên quan đến đường dẫn của loại sản phẩm.
			// MySQL Hierarchical Data Closure Table Pattern
			$level = 0;
			$parent_id = (int)$data['parent_id'];
			
			// Truy cập vào các bản ghi đường dẫn (category_id | path_id | level)
			// mà rễ của nó là loại cha
			$sql = " 
				SELECT * FROM `category_path` 
				WHERE `category_id` = $parent_id
				ORDER BY `level` ASC
			";
				
			$rs = db_q($sql);	// Truy vấn các bản ghi là cấp cha của loại sản phẩm vừa thêm vào
			
			foreach ($rs as $result) 
			{
				$path_id = (int)$result['path_id'];
				$sql = " 
					INSERT INTO `category_path` 
					SET `category_id` = {$category_id},
					    `path_id` = {$path_id},
						`level` = {$level}
				";
				db_q($sql);
				
				$level++;
			}
			
			$sql = " 
				INSERT INTO `category_path` 
				SET `category_id` = {$category_id},
				    `path_id` = {$category_id},
				    `level` = {$level}
			";
			db_q($sql);
			
			return $category_id;
} // end function 
	
/**
 * Sửa thông tin loại sản phẩm
 * 
 * @param int $category_id mã loại sản phẩm
 * @param array $data mảng thông tin mới của loại sản phẩm
 * 
 * @return void
 */
function categoryEdit($category_id, $data)
{	
		
			// Dữ liệu chỉnh sửa của loại sản phẩm cũ		
			$parent_id = (int)$data['parent_id'];
			$top = (isset($data['top']) ? (int)$data['top'] : 0);
			$column = (int)$data['column'];
			$sort_order = (int)$data['sort_order'];
			$status = (int)$data['status'];
			$featured = (int)$data['featured'];
			
			$name = db_escape($data['name']); 
			$description = db_escape($data['description']);
			
			$sql = " 
				UPDATE `category`
				SET `parent_id` = {$parent_id}, 
					`top` = {$top}, 
					`column` = {$column}, 
					`sort_order` = {$sort_order}, 
					`status` = {$status}, 
					`featured` = {$featured}, 
					`date_modified` = NOW(),
					`name` = '{$name}',
					`description` = '{$description}'
				WHERE category_id = '{$category_id}'
			";
			db_q($sql);
			
			// Cập nhật ảnh nếu có mới
			if (isset($data['image']))
			{	
				$image = db_escape($data['image']);
				$sql = " 
					UPDATE category 
					SET image = '{$image}' 
					WHERE category_id = '{$category_id}'
				";
				db_q($sql);
			}
			
			// MySQL Hierarchical Data Closure Table Pattern
			$sql = " 
				SELECT * FROM `category_path`  
				WHERE `path_id` = '{$category_id}'
				ORDER BY level ASC
			";
			$rs = db_q($sql);
			
			if ( is_array($rs) && !empty($rs) ) {
				foreach ( $rs as $category_path ) {
       				// Delete the path below the current one
       				$cid = (int)$category_path['category_id'];
       				$level = (int)$category_path['level'];
       				$sql = " 
						DELETE FROM `category_path`  
						WHERE `category_id` = '{$cid}' AND level < {$level}
					";
					db_q($sql);
					
					$path = array();
					
					// Get the nodes new parents
					$parent_id = (int)$data['parent_id'];
					$sql = " 
						SELECT * FROM `category_path`  
						WHERE `category_id` = '{$parent_id}'
						ORDER BY level ASC
					";
					$rs = db_q($sql);
					
					foreach ($rs as $result) {
						$path[] = $result['path_id'];
					}
					
					// Get whats left of the nodes current path
					$cid = (int)$category_path['category_id'];
					$sql = " 
						SELECT * FROM `category_path`  
						WHERE `category_id` = '{$cid}'
						ORDER BY `level` ASC
					";
					$rs = db_q($sql);
					
					foreach ($rs as $result) {
						$path[] = $result['path_id'];
					}
					
					// Combine the paths with a new level
					$level = 0;
					
					foreach ($path as $path_id) {
						$cid = (int)$category_path['category_id'];
						$sql = " 
							REPLACE INTO `category_path` 
							SET `category_id` = {$cid}, 
							    `path_id` = '{$path_id}', 
							    `level` = {$level}
						";
						db_q($sql);
						
						$level++;
					}
				}
			} 
			else {
				// Delete the path below the current one
				$sql = " 
					DELETE FROM `category_path` 
					WHERE `category_id` = '{$category_id}'
				";
				db_q($sql);
				
				// Fix for records with no paths
				$level = 0;
				$cid = (int)$data['parent_id'];
				$sql = " 
					SELECT * FROM `category_path`
					WHERE `category_id` = '$cid' 
					ORDER BY level ASC 
				";
				$rs = db_q($sql);
				
				foreach ($rs as $result) 
				{
					$pid = (int)$result['path_id'];
					$sql = "
						INSERT INTO `category_path` 
						SET `category_id` = '{$category_id}',
							`path_id` = {$pid},
							`level` = {$level}
					";
					db_q($sql);
					
					$level++;
				}
				
				$sql = " 
					REPLACE INTO `category_path`
					SET `category_id` = '{$category_id}',
						`path_id` = '{$category_id}',
						`level` = {$level}
				";
				db_q($sql);
			}
			
			db_q($sql);
			
} // end function

/**
 * Xóa thông tin loại sản phẩm
 * @param int $category_id
 */
function categoryDelete($category_id) 
{
		
			// Xóa đi các loại sản phẩm con của loại sản phẩm cần xóa.
			$sql = " 
				DELETE FROM `category_path`
				WHERE `category_id` = {$category_id}
			";
			db_q($sql);
			
			$sql = " 
				SELECT * FROM `category_path`
				WHERE path_id = {$category_id}
			";
			$rs = db_q($sql);
			if ( is_array($rs) && !empty($rs) ) {
				foreach ($rs as $result) {
					categoryDelete($result['category_id']);
				}
			}
			
			// Xóa đi mối liên hệ giữa loại sản phẩm này 
			// và các sản phẩm, các thực thể khác đã từng được liên kết với nó ...
			db_q("DELETE FROM `product_to_category` WHERE `category_id` = {$category_id}");
			
			// ...sau đó mới chính thức xóa đi loại sản phẩm
			db_q("DELETE FROM `category` WHERE `category_id` = {$category_id}");
			
			
} // end function

/**
 * Sửa lại mối quan hệ phân cấp của các loại sản phẩm trong trường hợp bị sai hỏng
 * @param number $parent_id
 * @return void
 */
function categoryRepair($parent_id = 0)
{
			$sql = " 
				SELECT * FROM `category`
				WHERE `parent_id` = {$parent_id}
			";
			$rs = db_q($sql);
					
			if ( is_array($rs) && !empty($rs) ) {
				
				foreach ($rs as $category) {
					// Delete the path below the current one
					$cid = (int)$category['category_id'];
					$sql = " 
						DELETE FROM `category_path`
						WHERE `category_id` = {$cid}
					";
					db_q($sql);
//		
					// Fix for records with no paths
					$level = 0;
					
					$sql = " 
						SELECT * FROM `category_path`
						WHERE `category_id` = {$parent_id}
						ORDER BY `level` ASC
					";
					$rs2 = db_q($sql);
//		
					foreach ($rs2 as $result) {
						$cid = (int)$category['category_id'];
						$path_id = (int)$result['path_id'];
						$sql = " 
							INSERT INTO `category_path`
							SET `category_id` = {$cid},
								`path_id` = {$path_id},
								`level` = {$level}
						";
						db_q($sql);
		
						$level++;
					}
//					
					$cid = (int)$category['category_id'];
					$sql = " 
						REPLACE INTO `category_path`
						SET `category_id` = {$cid},
							`path_id` = {$cid},
							`level` = {$level}
					";
					db_q($sql);
					
					categoryRepair($category['category_id']);
				} // end foreach
			} // end if

}
	
/**
 * @abstract Lấy ra các bản ghi để phân trang loại sản phẩm
 * SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;'
 * 
 * @return array indexed array of associative arrays
 * 
 * @error Column 'featured' in where clause is ambiguous
 *        Xử lý lỗi này bằng cách đặt tên bí danh của bảng trước các cột bị trùng tên.
 *        Khi liệt kê các cột này một cách tường minh sau từ khóa SELECT thì mọi chuyện OK:
 *        vừa liệt kê được cột mình muốn lấy tin, mà vừa tránh được lỗi bị trùng do có bí danh.
 *        
 * @desc Đừng bắt hàm này đọc cấu hình mặc định của hệ thống từ database.
 *       Ví dụ: $limit = 20, chứ đừng viết là $limit = $settings['config_admin_limit'].
 *       Nhường việc đọc dữ liệu db cho chương trình khách, cụ thể ở đây là tầng logic.
 */
function categoryGetAll($data = array())
{
		// phục vụ cho việc tìm kiếm sử dụng ajax để gợi nhắc tự động khi user gõ tên loại sản phẩm
		$filter_name = (empty($data['filter_name'])) ? '' : db_escape($data['filter_name']);
		
		// Câu truy vấn ban đầu chỉ đơn thuần tổng hợp các cột từ các bảng cần thiết
		$sql = "
			SELECT
				cd.name as name_no_path,
				cd.featured as featured,
				cd.status as status,
				cd.top as top,
				cd.image as image,
				cd.parent_id,
				cd.sort_order,
				cd.date_added,
				cp.category_id,
				GROUP_CONCAT(c.name ORDER BY cp.level SEPARATOR ' > ') AS name
			FROM `category_path` AS cp
			LEFT JOIN `category` AS c ON cp.path_id = c.category_id
			LEFT JOIN `category` AS cd ON cp.category_id = cd.category_id
			WHERE cd.name LIKE '%{$filter_name}%'
		";
		
		// Tiếp tục cập nhật, bổ sung câu truy vấn nếu phía khách đưa thêm nhiều tiêu chí lọc
		// Nếu yêu cầu lọc theo trạng thái của bài viết
		if (isset($data['filter_status']) && !is_null($data['filter_status']))
		{
			$sql .= " AND cd.status = '" . (int)$data['filter_status'] . "'";
		}
		
		// Nếu lọc theo tính nổi bật
		if (isset($data['filter_featured']) && !is_null($data['filter_featured']))
		{
			$sql .= " AND cd.featured = '" . (int)$data['filter_featured'] . "'";
		}
		
		// Nếu lọc theo tính chất top (được đưa lên menu trang chủ)
		if (isset($data['filter_top']) && !is_null($data['filter_top']))
		{
			$sql .= " AND cd.top = '" . (int)$data['filter_top'] . "'";
		}
		
		$sql .= " GROUP BY cp.category_id";
		
		// Trật tự sắp xếp và giới hạn phân trang mặc định.
		// Mặc định thì sắp xếp theo cột 'sort_order', sắp xếp tăng, 
		// bắt đầu từ bản ghi đầu tiên (index=0=$start), kết thúc ở bản ghi thứ 20 (index=19)
		$sort = "sort_order";
		$order = "ASC";
		$start = 0;
		$limit = 20;
		
		// Tinh chỉnh trật tự sắp xếp và giới hạn phân trang
		// dựa theo truy vấn gửi đến từ phía máy khách. 
		// Vì sao lại phải có bí danh 'cd' trước tên các cột, để đề phòng về sau nâng cấp nó lên thành phép nối bảng !!!
		// Mà khi nối bảng thì 2 bảng hay có các cột giống tên nhau, dễ gây lỗi Ambiguous.
		$sort_data = array(
			'cd.category_id',
			'cd.name',
			'cd.status',
			'cd.featured',
			'cd.top',
			'cd.sort_order',
			'cd.date_added'
		);                    // Các cột được phép sắp xếp.
		
		// Nếu phía khách yêu cầu cột sắp xếp khác
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) 
		{
			$sort = $data['sort'];
		}
		// Nếu phía khách yêu cầu trật tự sắp xếp khác
		if (isset($data['order']) && ($data['order'] == 'DESC')) 
		{
			$order = "DESC";
		}
		// Nếu phía khách chỉ định giá trị khác cho chỉ số của trang hiện tại
		if (isset($data['start']) && (int)$data['start'] >= 0)
		{
		    $start = (int)$data['start'];
		}
		// Nếu phía khách chỉ định giá trị khác cho kích thước phân trang
		if (isset($data['limit']) && (int)$data['limit'] >= 1)
		{
		    $limit = (int)$data['limit'];
		}
		
		// Cập nhật câu truy vấn theo yêu cầu phía khách
		$sql .= " ORDER BY {$sort} {$order}"; // ví dụ: ORDER BY name ASC
		$sql .= " LIMIT {$start},{$limit}";
		
			// echo $sql; // test only
			
		/*
		 MySQL Hierarchical Data Closure Table Pattern.
		 Nối bảng `category_path` và bảng `category` trên quan hệ category_path.path_id = category.category_id để lấy tên của path
		 Nối bảng `category_path` và bảng `category` trên quan hệ category_path.category_id = category.category_id để lấy thêm
		 	các thông tin khác của loại sản phẩm (category)
		 Ví dụ:
		 Để tạo nên đường dẫn phân cấp loại sản phẩm: 
		 		path name:  /Components/Monitors/Test1
		 		path id:    /25/28/35
		 		path level: /0/1/2
		 thì cần 3 bản ghi trong bảng category_path:
		 
		 path_id | category_id | level
		 -----------------------------
		 35      | 35          | 2
		 28      | 35          | 1
		 25      | 35          | 0
		 
		 tham khảo: https://bojanz.wordpress.com/2014/04/25/storing-hierarchical-data-materialized-path/
		 */
		/*
		$sql = " 
			SELECT 
                cd.name as name_no_path,
				cd.featured as featured,
			    cd.status as status,
				cd.top as top,
				cd.image as image,
				cd.parent_id,
				cp.category_id,
			    GROUP_CONCAT(c.name ORDER BY cp.level SEPARATOR ' > ') AS name,
			    cd.sort_order
			FROM `category_path` AS cp
			LEFT JOIN `category` AS c ON cp.path_id = c.category_id
			LEFT JOIN `category` AS cd ON cp.category_id = cd.category_id
			WHERE cd.name LIKE '%{$filter_name}%'
			GROUP BY cp.category_id
			ORDER BY {$sort} {$order}
			LIMIT {$start},{$limit}
		";
		*/
			
		
		
//		echo $sql;
		$rs = db_q($sql);
			
		if ( is_array($rs) && !empty($rs) ) 
		{
			return $rs;
		}
			
		return false;
}// end function
	
/**
 * Đếm tổng số loại sản phẩm
 * 
 * @return number if successfully queried
 * @return int 0 if there are no records
 * @return NULL if there is error with query (Ex: SELECT COUNT(*) FROM categoryXXX WHERE 3 < 1)
 */
function categoryGetTotal($data=array())
{
	$filter_name = "%".db_escape($data['filter_name']) . "%";
	
	$sql = "
		SELECT COUNT(*) 
		FROM `category`
		WHERE `name` LIKE '{$filter_name}'
    ";
	
	if (isset($data['filter_status']) && !is_null($data['filter_status']))
	{
		$sql .= " AND `status` = '" . (int)$data['filter_status'] . "'";
	}
	
	if (isset($data['filter_featured']) && !is_null($data['filter_featured']))
	{
		$sql .= " AND `featured` = '" . (int)$data['filter_featured'] . "'";
	}
	
	if (isset($data['filter_top']) && !is_null($data['filter_top']))
	{
		$sql .= " AND `top` = '" . (int)$data['filter_top'] . "'";
	}
		// echo $sql; // test only
	$rs = db_one($sql);
			
	if ( !is_null($rs) ) 
	{
		return $rs;
	}

	return false;
}
	
/**
 * @return array Mảng kết hợp biểu diễn thông tin của một loại sản phẩm
 */
function categoryGetById($category_id)
{
		$category_id = (int)$category_id;
		$sql = " 
			SELECT DISTINCT *, 
			 (
			  SELECT GROUP_CONCAT(cd1.name ORDER BY `level` SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') 
			  FROM `category_path` cp 
			  LEFT JOIN `category` cd1 ON (cp.path_id = cd1.category_id AND cp.category_id != cp.path_id) 
			  WHERE cp.category_id = c.category_id
			  GROUP BY cp.category_id
			 ) AS path 
			FROM `category` c
			WHERE c.category_id = {$category_id}
		";

		$rs = db_row($sql);
		if ( is_array($rs) && !empty($rs) ) 
		{
				return $rs;
		}

		return false;
}

/**
 * 
 * @param int $category_id
 * @return array();|boolean Mảng kết hợp thông tin của một loại sản phẩm
 */
function categoryGetByIdForPublic($category_id) 
{
	$sql = " 
		SELECT DISTINCT * FROM `category` 
		WHERE `category_id` = '" . (int)$category_id . "' AND `status` = '1'
    ";
	
	$rs =  db_row($sql);
	
	if ( is_array($rs) && !empty($rs) ) 
	{
		return $rs;
	}

	return false;
}

/**
 * @return array một mảng kết hợp các loại sản phẩm con
 * @param number $parent_id
 */
function categoryGetAllForPublic($parent_id = 0)
{
	$sql = " 
			SELECT * FROM `category`
			WHERE `parent_id` = '{$parent_id}' 
				AND `status` = '1' 
			ORDER BY `sort_order`, LCASE(name)
	";

	$rs = db_q($sql);
	
	if ( is_array($rs) && !empty($rs) )
	{
		return $rs;
	}

	return false;
} // en

/**
 * 
 * @param number $parent_id
 * @return array|boolean
 */
function categoryGetAllByParent($parent_id = 0)
{
	$sql = " 
		SELECT * FROM `category`
		WHERE `parent_id` = '{$parent_id}' 
				AND `status` = '1' 
		ORDER BY `sort_order`, LCASE(name)
	";

	$rs = db_q($sql);
	
	if ( is_array($rs) && !empty($rs) )
	{
		return $rs;
	}

	return false;
} // en

/**
 * Xem: https://demo.opencart.com/
 * 
 *  Ví dụ: loại cha cat_id=34 có 18 loại con. Ấn định số cột nhóm chia ra là 4 cột thì 
 *  mỗi cột sẽ có số phần tử là ceil(18/4) = 5 phần tử. Cột cuối có thể có ít hơn 5 phần tử.
 *  
 *  Hàm array_chunk() trên mảng children và tham số 5 sẽ tách ra thành 4 cột, mỗi cột 5 phần tử.
 * Những loại sản phẩm được đưa lên menu phải có: parent_id=0, status=1, top=1 
 * 
 * Những cách để giới hạn số menu top:
 * - hard code, truyền đối số cho $limit khi gọi hàm này trong file /ui/home/opencart_000/layout.php
 *   cách này gây ảnh hưởng mạnh nhất, ngay cả adminh cũng không thay đổi được.
 * - chọn thuộc tính 'top' trên màn hình quản trị: /admin/category-edit.php
 *   cách này linh hoạt, dành cho nhân viên quản trị.
 *   
 * Vậy nên là: gọi hàm này mà không truyền đối số cho $limit, từ màn hình admin tinh chỉnh 'top' để quyết định xem loại nào được hiện.
 */
function categoryGetAllForMenuHomePage($limit = 10)
{
	// Mảng chứa những loại sản phẩm xuất hiện trên Menu Top trang chủ.
	$categoriesForTopMenu = array();

	// Lấy ra những loại cấp cao nhất (không có cha): Level 1
	$categories = categoryGetAllByParent(0);

	// Tiếp tục lọc ra những loại top trong số level 1 đó
    foreach ($categories as $category) 
	{
	     if ($category['top']) 
	     {
		      // Level 2
		      $children_data = array();
	
			  // Với mỗi loại top thì lấy ra những loại con của nó
			  //$children = $categories = categoryGetAllByParent($category['category_id']);
		      $children = categoryGetAllByParent($category['category_id']);
	
			  // Với mỗi loại con thì bổ sung thống kê số sản phẩm của nó ngay bên cạnh tên loại.
			  // và đường dẫn đến loại sản phẩm đó.
			  foreach ($children as $child) 
			  {
	
			       $children_data[] = array(
				        'name'  => $child['name'] . ' ('. productGetTotalForCategory($child['category_id']) . ')',
						'href'  => '/product-category.php?path=' . $category['category_id'] . '_' . $child['category_id']
						);
				   }
	
				   // Level 1
				   // Tinh chỉnh lại thông tin của mỗi loại top & level 1:
				   // @todo có thể bổ sung 'children_per_col' = ceil(count($children_data)/category['column'])
				   //       để giảm tải tính toán cho phía giao diện
				   $categoriesForTopMenu[] = array(
						'name'     => $category['name'],	// Tên loại
						'children' => $children_data,		// Mảng các loại con
						'column'   => $category['column'] ? $category['column'] : 1,	// Số cột được cấp trên Menu Top để chứa các loại con vào đó.
						'href'     => '/product-category.php?path=' . $category['category_id']	// đường dẫn đến loại cha.
				   );
		}
	}
	
	// Nhiều giao diện có menu rất hẹp, không đủ chỗ cho tất cả các mục
	// vì vậy phải giới hạn và đặt ra độ ưu tiên (trật tự sắp xếp giữa các menu) thì mới đủ
	// Nếu số menu level 1 thuộc hàng top tìm thấy nhiều hơn giới hạn cho phép thì phải cắt bớt.
	if ($limit < count($categoriesForTopMenu)) 
	{
		return array_slice($categoriesForTopMenu, 0, $limit);
	}
	
	return $categoriesForTopMenu;
}

/**
 * @abstract Hàm này nhận đầu vào là các thông tin của 1 loại sản phẩm : dài, rộng, giới hạn mô tả
 * Có thể giới hạn số loại nổi bật thông qua màn hình quản trị /admin/category-edit.php, thuộc tính 'featured'
 * Mặc định thì ảnh đại diện dài 160, rộng 90, text mô tả: 100 kí tự, giới hạn mặc định: 3 loại nổi bật được phép hiện.
 * Đừng gọi settings('categories_featured_limit') ở đây, để việc đó cho phía khách.
 * 
 * @version 2021.04.28 10h12
 * @return array Trả về một mảng các Loại Sản Phẩm Nổi Bật.
 * 
 */
//function categoryFeatureds( $data=array('limit'=>3, 'width'=>160,'height'=>90, 'text_length'=>100) )
function categoryFeatureds( $data=array() )
{
    // Tiền xử lý dữ liệu của các tham số đầu vào. Việc này độc lập với việc khai báo tham số $data của hàm.
    // Ngay cả khi $data không được khai báo trong nguyên mẫu hàm thì hàm này vẫn hoạt động tốt và ổn định.
    // ví dụ: categoryFeatureds( $data=array() ) hoặc categoryFeatureds()
    
    // Nếu giá trị của các tham số đầu vào có vấn đề, không hợp lệ thì thay thế bằng giá trị mặc định
    $limit = ( isset($data['limit']) && (int)$data['limit'] > 0 ) ? (int)$data['limit'] : 3;
    $width = ( isset($data['width']) && (int)$data['width'] > 0 ) ? (int)$data['width'] : 160;
    $height = ( isset($data['height']) && (int)$data['height'] > 0 ) ? (int)$data['width'] : 90;
    
    if(isset($data['text_length']) && (int)$data['text_length'] > 0)
    {
        $text_length = (int)$data['text_length'];
    } else {
        $text_length = 100;
    }
    
    // Câu truy vấn tường minh, liệt kê rõ các cột cần lấy dữ liệu, 
    // không nên viết kiểu ngầm định: SELECT DISCTINCT *
	$sql = "
		SELECT 
            c.category_id, c.name, c.image, c.description
		FROM `category` AS c
		WHERE c.status = 1 AND c.featured = 1
	";
	
	// truy vấn dữ liệu thô
	$rs = db_q($sql);
	
	// bổ sung dữ liệu phái sinh, có định dạng dễ hiểu với người dùng cuối.
	// Duyệt mảng dữ liệu thô theo kiểu tham chiếu (&) thì mới can thiệp, chỉnh sửa, bổ sung
	// dữ liệu gốc của mỗi dòng bản ghi truy vấn được.
	foreach ($rs as &$row)
	{
	    $row['thumb'] = url_image_resized($row['image'], $width, $height);
	    $row['url_image_resized'] = url_image_resized($row['image'], $width, $height);
	    $row['desc_short'] = text_short($row['description'],$text_length);
	    $row['href'] = "/product-category.php?path=".$row['category_id'];
	}
	
	// Cắt bớt mảng theo giới hạn mà phía chương trình khách yêu cầu.
	$rs = array_slice($rs, 0, $limit); 
	
	return $rs;
	
}// end function

/**
 * @abstract Lấy ra các loại mà có ít nhất một sản phẩm.
 *           Nghĩa là tồn tại ít nhất 1 sản phẩm thuộc về loại đó.
 *
 * @param array $data Mảng tham số đầu vào.
 * @return array Trả về 1 mảng mà mỗi phần tử của nó là dữ liệu loại sản phẩm.
 *
 * Câu sql thống kê xem mỗi loại có những sản phẩm nào:
 *
 SELECT
 	DISTINCT ptc.category_id,
    c.name,
    c.sort_order,
    c.image,
    c.description
 FROM `product_to_category` as ptc
 LEFT JOIN `category` as c ON ptc.category_id = c.category_id
 */
function categoryGetAllThatHaveProduct($data = array())
{
	// phục vụ cho việc tìm kiếm sử dụng ajax để gợi nhắc tự động khi user gõ tên loại sản phẩm
	//$filter_name = (empty($data['filter_name'])) ? '' : db_escape($data['filter_name']);
	
	// Mặc định sắp xếp theo cột 'sort_order', thứ tự tăng dần
	$sort = "sort_order";
	$order = "ASC";
	
	// Tinh chỉnh trật tự sắp xếp và giới hạn phân trang
	// dựa theo truy vấn gửi đến từ phía chương trình khách.
	$sort_data = array(
		'name',
		'sort_order'
	);
	
	if (isset($data['sort']) && in_array($data['sort'], $sort_data)) 
	{
		$sort = $data['sort'];
	}
	if (isset($data['order']) && ($data['order'] == 'DESC')) 
	{
		$order = "DESC";
	}
	
	// Mặc định phân trang 20 phần tử trên trang,
	// bắt đầu từ trang đầu tiên.
	$start = 0;
	$limit = 20;
	
	// Nếu phía chương trình khách yêu cầu, chỉ định các giá trị khác
	// cho việc phân trang:
	if (isset($data['start']) && (int)$data['start'] >= 0)
		$start = (int)$data['start'];
		
	if (isset($data['limit']) && (int)$data['limit'] >= 1)
		$limit = (int)$data['limit'];
			
	$sql = "
		SELECT 
			DISTINCT ptc.category_id,
			c.name,
			c.sort_order,
			c.image,
			c.description
		FROM `product_to_category` as ptc
		LEFT JOIN `category` as c ON ptc.category_id = c.category_id
		ORDER BY {$sort} {$order}
		LIMIT {$start},{$limit}
	";
			
	//		echo $sql;
	$rs = db_q($sql);
			
	if ( is_array($rs) && !empty($rs) )
	{
		return $rs;
	}
			
	return false;
}// end function