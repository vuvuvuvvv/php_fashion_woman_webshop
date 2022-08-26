<?php
/**
 * Copyright C2009G
 *
 * Các hàm quản lý nhà sản xuất
 */

/**
 * @abstract Hàm thêm mới nhà sản xuất
 * 
 * @param array $data (Mảng kết hợp chứa dữ liệu của một nhà sản xuất mới)
 * 
 * @return int Manufacturer Identifier (Mã định danh của nhà sản xuất)
 */
function manufacturerAdd($data) 
{

	// Xử lý dữ liệu thô gửi lên từ form
	$name       = db_escape($data['name']);
	$sort_order = (int)$data['sort_order'];	// thứ tự sắp xếp
	$featured   = (int)$data['featured'];   // tính nổi bật (có/không)
	
	// Thêm mới bản ghi vào cơ sở dữ liệu
	$sql = "
		INSERT INTO `manufacturer` 
	 	SET `name` = '{$name}', 
	 	    `sort_order` = '{$sort_order}', 
	 	    `featured`='{$featured}'
	";
	db_q($sql);

	$manufacturer_id = (int)db_lastId();
	
	// Ảnh đại diện nhà sản xuất
	if (isset($data['image'])) 
	{
		$image = db_escape($data['image']);
		db_q("UPDATE `manufacturer` SET `image` = '{$image}' WHERE `manufacturer_id` = '{$manufacturer_id}'");
	}

	return $manufacturer_id;
	
} // kết thúc hàm thêm mới

/**
 * @abstract Hàm sửa nhà sản xuất 
 * @param int $manufacturer_id (Mã định danh của nhà sản xuất)
 * @param array $data (Mảng kết hợp chứa dữ liệu cập nhật của nhà sản xuất cần sửa)
 *
 * @return void
 */
function manufacturerEdit($manufacturer_id, $data) 
{ 
	$name       = db_escape($data['name']);
	$sort_order = (int)$data['sort_order'];
	$featured   = (int)$data['featured'];
	$mid        = (int)$manufacturer_id;
		
	$sql = "
		UPDATE `manufacturer` 
		SET `name` = '{$name}', 
		    `sort_order` = '{$sort_order}', 
		    `featured`='{$featured}' 
		WHERE `manufacturer_id` = '{$mid}'		
	";
	
	db_q($sql);

	// Ảnh đại diện nhà sản xuất
	if (isset($data['image'])) 
	{
		$image = db_escape($data['image']);
		db_q("UPDATE `manufacturer` SET `image` = '{$image}' WHERE `manufacturer_id` = '{$mid}'");
			
	}

}

/**
 * @abstract Hàm xóa nhà sản xuất
 * @param int $manufacturer_id (Mã định danh của nhà sản xuất)
 * @param array $data (Mảng kết hợp chứa dữ liệu cập nhật của nhà sản xuất cần sửa)
 *
 * @return void
 */
function manufacturerDelete($manufacturer_id) 
{
	// @todo xóa đi các bản ghi liên quan đến nhà sản xuất này trước.
	// ví dụ: các sản phẩm thuộc về nhà sản xuất này
	
	db_q("DELETE FROM `manufacturer` WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");
}

/**
 * @param int $manufacturer_id
 * @return boolean|array
 * 
 * @abstract Trả về một mảng kết hợp chứa dữ liệu của một nhà sản xuất
 */
function manufacturerGetById($manufacturer_id)
{
	$sql = " 
		SELECT DISTINCT * 
		FROM manufacturer 
		WHERE manufacturer_id = {$manufacturer_id}
	";
	
	$rs = db_row($sql);
	if ( is_array($rs) && !empty($rs) ) 
	{
		return $rs;
	}

	return false;
} // end getManufacturer($manufacturer_id)

/**
 * @param associative array $data Mảng chứa tham số phân trang, sắp xếp, tìm kiếm
 * @return int|boolean
 * @abstract Đếm tổng số nhà sản xuất trong db.
 */
function manufacturerGetTotal($data = array())
{
	// Phục vụ tìm kiếm Ajax (ví dụ chọn nhà sản xuất cho sản phẩm)
	$filter_name = "%".db_escape($data['filter_name']) . "%";
	
	$sql = "
		SELECT COUNT(*) AS total 
		FROM `manufacturer`
		WHERE `name` LIKE '{$filter_name}'	
	";
	
	// Nếu yêu cầu tìm theo tính nổi bật của nhà sản xuất 
	// thì bổ sung yêu cầu này vào câu sql
	if (isset($data['filter_featured']) && !is_null($data['filter_featured']))
	{
		$sql .= " AND `featured` = '" . (int)$data['filter_featured'] . "'";
	}
	
	$rs = db_one($sql);
		
	if ( !is_null($rs) ) 
	{
		return $rs;
	}

	return false;
}
/**
 * @param associative array $data Mảng chứa tham số phân trang, sắp xếp, tìm kiếm
 * @abstract Lấy ra các bản ghi để phân trang
 * 
 * @returns NULL|an indexed array of associative arrays
 * @notice sort_data must work closely with html view form, url parameters
 * 
 * @synchronize with getTotalManufacturers($data)
 */
function manufacturerGetAll($data = array())
{
		
	// Phục vụ tìm kiếm Ajax (ví dụ chọn nhà sản xuất cho sản phẩm)
	$filter_name = "%".db_escape($data['filter_name']) . "%";
		
	// Nhúng từ khóa tìm kiếm vào câu truy vấn sql
	$sql = "
		SELECT *
		FROM `manufacturer`
		WHERE name LIKE '{$filter_name}'
	";
	
	// Nếu yêu cầu tìm theo tính nổi bật của nhà sản xuất
	// thì bổ sung yêu cầu này vào câu sql
	if (isset($data['filter_featured']) && !is_null($data['filter_featured']))
	{
		$sql .= " AND `featured` = '" . (int)$data['filter_featured'] . "'";
	}
		
    // @notice sort_data must work closely with html view form, url parameters
    // Các cột được phép sắp xếp
	$sort_data = array(
		'name',
		'featured',
		'sort_order'	// @check it out
	);
		
	// Nếu phía giao diện chỉ định cột sắp xếp
	if (isset($data['sort']) && in_array($data['sort'], $sort_data)) 
	{
		$sql .= " ORDER BY " . $data['sort'];
	} 
	else // còn không nói gì thì sắp xếp theo tên nhà sản xuất
	{
		$sql .= " ORDER BY name";
	}

	// Nếu phía giao diện chỉ định chiều sắp xếp là tăng dần
	if (isset($data['order']) && ($data['order'] == 'DESC')) 
	{
		$sql .= " DESC";
	} 
	else // còn không nói gì thì sắp xếp giảm dần
	{
		$sql .= " ASC";
	}
	
	// Khởi tạo giá trị mặc định cho các tham số phân trang 
	$start = 0;
	$limit = settings('config_limit_admin');
		
	// Nếu phía giao diện chỉ định / yêu cầu tham số phân trang khác
	if (isset($data['start']) && (int)$data['start'] > 0)
		$start = (int)$data['start'];
			
	if (isset($data['limit']) && (int)$data['limit'] >= 1)
		$limit = (int)$data['limit'];
			
	$sql .= " LIMIT {$start},{$limit}";
		
	$rs = db_q($sql);
			
	if ( is_array($rs) && !empty($rs) ) 
		return $rs;

	return NULL;
} // end getManufacturers

/**
 * @param void
 * @return array Mảng các nhà sản xuất nổi bật.
 * 
 * @abstract Trả về mảng các nhà sản xuất nổi bật (để chạy slideshow trên trang chủ chẳng hạn)
 * @version 2021.05.16 21h15
 */
function manufacturerFeatureds( $data=array() )
{
    // Nếu giá trị của các tham số đầu vào có vấn đề, không hợp lệ thì thay thế bằng giá trị mặc định
    $limit = ( isset($data['limit']) && (int)$data['limit'] > 0 ) ? (int)$data['limit'] : 9;
    $width = ( isset($data['width']) && (int)$data['width'] > 0 ) ? (int)$data['width'] : 160;
    $height = ( isset($data['height']) && (int)$data['height'] > 0 ) ? (int)$data['height'] : 90;
    
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
            `manufacturer_id`,
            `image`,
            `name`
        FROM `manufacturer` WHERE `featured` = 1
	";

	// Lấy ra danh sách id các sản phẩm nổi bật
	// và giới hạn số lượng hiển thị trên html.
	$rs = db_q($sql);

	// bổ sung dữ liệu phái sinh, có định dạng dễ hiểu với người dùng cuối.
	// Duyệt mảng dữ liệu thô theo kiểu tham chiếu (&) thì mới can thiệp, chỉnh sửa, bổ sung
	// dữ liệu gốc của mỗi dòng bản ghi truy vấn được.
	// Định dạng lại dữ liệu của mỗi bản ghi để dễ hiểu hơn với người dùng cuối
	foreach ($rs as &$row)
	{
			
	    $row['url_image_resized'] = url_image_resized($row['image'], $width, $height);
	    $row['thumb'] = url_image_resized($row['image'], $width, $height);
	    $row['link']              = "/manufacturer-info.php?manufacturer_id=" . $row['manufacturer_id'];
	    $row['href']              = "/manufacturer-info.php?manufacturer_id=" . $row['manufacturer_id'];
	}
	
	// @todo manufacturers_featured_limit
	$rs = array_slice($rs, 0, $limit);
	return $rs;
	
}// end function manufacturerFeatureds()