
<div id="information-contact" class="container">
    <ul class="breadcrumb">
        <li>
            <a href="/home.php"><i class="fa fa-home"></i></a>
        </li>
        <li><a href="/contact.php">Liên Hệ</a></li>
    </ul>
    <div class="row">
        <div id="content" class="col-sm-12">
            <!-- h1>Liên Hệ Với Chúng Tôi</h1 -->
            <h3>Liên Hệ Với Chúng Tôi</h3>
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-3">
                            <strong>Địa Chỉ</strong><br />
                            <address>
                                <?php echo $settings['config_address'];?><br/>
                            </address>
                        </div>
                        <div class="col-sm-3">
                            <strong>Điện Thoại</strong><br />
                            <?php echo $settings['config_telephone'];?><br />
                            <br />
                        </div>
                        <div class="col-sm-3">
							<strong>Email</strong><br />
                            <a href="mailto:cskh@gmail.com"><?php echo $settings['config_email'];?></a><br />
                            <br />
						</div>
                    </div>
					<div class="row">
							<div class="col-sm-12">
						
								<!-- Tham khảo cách nhúng bản đồ Google Map vào html
								https://support.google.com/maps/answer/144361?hl=vi&co=GENIE.Platform%3DDesktop
								 -->
								<?php echo $settings['html_google_map_embed'];?>
							</div>
					</div>
                </div>
            </div>
            <form action="/contact.php" method="post" enctype="multipart/form-data" class="form-horizontal">
                <fieldset>
                    <legend>Phản Hồi Khách Hàng (Feedback Form)</legend>
                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-name">Tên</label>
                        <div class="col-sm-10">
                            <input name="name" value="" id="input-name" class="form-control" type="text" />
                        </div>
                    </div>
                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-email">E-Mail</label>
                        <div class="col-sm-10">
                            <input name="email" value="" id="input-email" class="form-control" type="text" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-phone">Điện Thoại</label>
                        <div class="col-sm-10">
                            <input name="phone" value="" id="input-phone" class="form-control" type="text" />
                        </div>
                    </div>
                    

                    <div class="form-row">
                        <label class="col-sm-2 control-label" for="input-date">Ngày (dd/mm/yyyy)</label>
                        <div class="form-group col-sm-2">
                              <input name="date" value="<?php echo $date;?>" placeholder="Ngày hẹn gặp, tiếp nhận, v.v..." id="input-date" class="form-control" type="date" >
                         </div>

                         <label class="col-sm-2 control-label" for="input-time">Thời Gian (HH:ii)</label>
                          <div class="form-group col-md-6">
                              <input name="time" value="<?php echo $time;?>" placeholder="Thời gian hẹn gặp, tiếp nhận, v.v..." id="input-time" class="form-control" type="time">
                          </div>
                    </div>

					<div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-subject">Tiêu Đề</label>
                        <div class="col-sm-10">
                            <input name="subject" value="" id="input-subject" class="form-control" placeholder="Góp Ý, Phản Hồi, Khiếu Nại, Hẹn Gặp, v.v..." type="text" />
                        </div>
                    </div>
                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-message">Nội Dung</label>
                        <div class="col-sm-10">
                            <textarea name="message" rows="10" id="input-message" class="form-control"></textarea>
                        </div>
                    </div>
                </fieldset>
                <div class="buttons">
                    <div class="pull-right">
                        <input class="btn btn-primary" type="submit" value="Gửi" />
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<script type="text/javascript">

</script>