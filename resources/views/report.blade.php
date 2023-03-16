<?php
/** @var $date **/


?>

<div class="row">
    <div class="col-lg-8 sebes-menu-header">
        <form method="GET">
            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label">Дата отчета</label>
                <div class="col-lg-3">
                    <input type="text" name="date" value="<?=$date?>" class="form-control"  style="height: 36px !important" >
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <input type="submit" class="btn btn-default" name=report value="Построить отчет">
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="col-lg-12">
        Было выполнено {{$orders}} заказа на общую сумму {{$price}}
    </div>

</div>
