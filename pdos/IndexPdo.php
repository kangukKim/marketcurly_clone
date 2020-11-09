<?php


//validation
function isValidUserId($userId){
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from User where userId = ?) exist;";
    $st = $pdo->prepare($query);
    $st->execute([$userId]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    if($userId==null){
        $st = null;
        $pdo = null;
        return array(false, "아이디를 입력해주세요.",401);
        exit;
    }
    if($res[0]['exist']==1){
        $st = null;
        $pdo = null;
        return array(false, "이미 존재하는 ID 입니다.",402);
        exit;
    }
    $pattern1 = '/^[0-9A-Za-z]{6,20}$/u';
    $pattern2='/[a-zA-Z]/u';
    if(!preg_match($pattern1 ,$userId)){
        $st = null;
        $pdo = null;
        return array(false, "ID는 6자리 이상 영문자 또는 영문자와 숫자 조합만 가능합니다.",403);
        exit;
    }
    if(!preg_match($pattern2 ,$userId)){
        $st = null;
        $pdo = null;
        return array(false, "ID는 영문자를 반드시 포함해야합니다.",404);
        exit;
    }
    return array(True, "사용가능한 ID 입니다.",200);
}

function isValidUserIdx($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from User where userIdx = ? and isDeleted='N') exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}
//PATCH
function changeBasket($userIdx,$option){
    $pdo = pdoSqlConnect();
    for($i=0;$i<count($option);$i++){
        if($option[$i]->optionCount==0){
            $query = "select optionName from ProductOption where optionIdx = ?;";
            $st = $pdo->prepare($query);
            $st->execute([$option[$i]->optionIdx]);
            $st->setFetchMode(PDO::FETCH_ASSOC);
            return array(false, strval($st->fetchAll()[0]['optionName'])."의 개수를 1개 이상 선택하고 다시 시도해주십시오.",423);
        }
        $query = "select EXISTS(select * from ProductOption where optionIdx = ? and isDeleted='N') exist;";
        $st = $pdo->prepare($query);
        $st->execute([$option[$i]->optionIdx]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $bool = $st->fetchAll()[0]['exist'];
        if(!$bool){
            return array(false, "존재하지 않는 제품이 섞여있습니다.",420);
        }
        $query = "select EXISTS(select * from Basket where userIdx = ? and optionIdx = ? and isDeleted='N') exist;";
        $st = $pdo->prepare($query);
        $st->execute([$userIdx,$option[$i]->optionIdx]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $bool = $st->fetchAll()[0]['exist'];
        if(!$bool){
            $query = "select optionName from ProductOption where optionIdx = ?;";
            $st = $pdo->prepare($query);
            $st->execute([$option[$i]->optionIdx]);
            $st->setFetchMode(PDO::FETCH_ASSOC);
            return array(false, strval($st->fetchAll()[0]['optionName'])."는 장바구니에 존재하지 않는 제품입니다.",422);
        }
    }
    try{
        $pdo->beginTransaction();
        for($i=0;$i<count($option);$i++){
            $query = "update Basket set needCount = ? where userIdx=? and optionIdx=?;";
            $st = $pdo->prepare($query);
            $st->execute([$option[$i]->optionCount,$userIdx, $option[$i]->optionIdx]);
        }
        $pdo->commit();
        return array(True,"장바구니가 업데이트 되었습니다.",200);
    }
    catch ( PDOException $e ) {
        // Failed to insert the order into the database so we rollback any changes
        $pdo->rollback();
        throw $e;
    }
}

//DELETE
function deleteBasket($userIdx, $option){
    $pdo = pdoSqlConnect();
    for($i=0;$i<count($option);$i++){
        $query = "select EXISTS(select * from ProductOption where optionIdx = ? and isDeleted='N') exist;";
        $st = $pdo->prepare($query);
        $st->execute([$option[$i]->optionIdx]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $bool = $st->fetchAll()[0]['exist'];
        if(!$bool){
            return array(false, "존재하지 않는 제품이 섞여있습니다.",420);
        }
        $query = "select EXISTS(select * from Basket where userIdx = ? and optionIdx = ? and isDeleted='N') exist;";
        $st = $pdo->prepare($query);
        $st->execute([$userIdx,$option[$i]->optionIdx]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $bool = $st->fetchAll()[0]['exist'];
        if(!$bool){
            $query = "select optionName from ProductOption where optionIdx = ?;";
            $st = $pdo->prepare($query);
            $st->execute([$option[$i]->optionIdx]);
            $st->setFetchMode(PDO::FETCH_ASSOC);
            return array(false, strval($st->fetchAll()[0]['optionName'])."는 장바구니에 존재하지 않는 제품입니다.",422);
        }
    }
    try{
        $pdo->beginTransaction();
        for($i=0;$i<count($option);$i++){
            $query = "update Basket set isDeleted = 'Y' where userIdx=? and optionIdx=?;";
            $st = $pdo->prepare($query);
            $st->execute([$userIdx, $option[$i]->optionIdx]);
        }
        $pdo->commit();
        return array(True,"선택한 제품을 삭제하였습니다.",200);
    }
    catch ( PDOException $e ) {
        // Failed to insert the order into the database so we rollback any changes
        $pdo->rollback();
        throw $e;
    }
}
//GET
function getCoupon($userIdx){
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from UserCoupon where userIdx = ? and isUsed='N' and isDeleted='N') exist;";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $bool = $st->fetchAll()[0]['exist'];
    if(!$bool){
        return array(false, "쿠폰이 존재하지 않습니다.",427);
    }
    $query="select UserCoupon.couponIdx, R2.couponName, R2.contents, ifnull(R.discount,0) as discount, date_format(R2.expiration, '%Y년 %m월 %d일 %h시 만료') as expiration, if(isnull(R.couponIdx),'N','Y') as isAvailable from UserCoupon
    inner join
    (select couponName,contents,couponIdx,expiration from Coupon) as R2
    on R2.couponIdx=UserCoupon.couponIdx
    left outer join
(select couponName, contents, couponIdx, cast(if(if(isnull(P.discountPercent),discountPrice,productPrice*discountPercent/100)>maxDiscount,maxdiscount,
        if(isnull(P.discountPercent),discountPrice,productPrice*discountPercent/100)) as unsigned int) as discount, expiration from
(select couponName, contents, userIdx,UserCoupon.couponIdx as couponIdx, ifnull(sum(needCount),(select sum(needCount) as needCount from Basket where userIdx=? and Basket.isDeleted='N'))  as needCount,
       ifnull(sum(productPrice),(select sum(P1.clientPrice*Basket.needCount) as productPrice from Basket
           inner join (select optionIdx,clientPrice from ProductOption) as P1
on Basket.optionIdx=P1.optionIdx where userIdx=? and Basket.isDeleted='N')) as productPrice,discountPercent,discountPrice,minPrice,maxDiscount,expiration from UserCoupon
left outer join (select couponIdx , couponName, discountPercent, discountPrice, contents, minCount, minPrice, maxDiscount, expiration from Coupon) as P1
on P1.couponIdx=UserCoupon.couponIdx
left outer join (select couponIdx,productIdx from CouponProduct) as P2
on P2.couponIdx=UserCoupon.couponIdx
left outer join (select productIdx,count(needCount) as needCount from Basket group by productIdx) as P3
on P3.productIdx=P2.productIdx
left outer join (select productIdx, sum(P5.clientPrice*Basket.needCount) as productPrice from Basket inner join (select optionIdx,clientPrice from ProductOption) as P5
on Basket.optionIdx=P5.optionIdx
group by productIdx) as P4
on P2.productIdx=P4.productIdx
where userIdx=? and UserCoupon.isUsed='N' and UserCoupon.isDeleted='N' and (isnull(P2.productIdx) or P2.productIdx in (select productIdx from Basket where userIdx=?))
group by UserCoupon.couponIdx,minPrice,minCount
having if(minPrice=0, needCount>=minCount,productPrice>=minPrice))as P) as R
on R.couponIdx=UserCoupon.couponIdx
where userIdx=? and isUsed='N'and isDeleted='N' and timestampdiff(minute, R2.expiration, now())<0;";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$userIdx,$userIdx,$userIdx,$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    return array(True, "쿠폰 목록입니다.",$res);

}


function getPay($userIdx){
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from Basket where userIdx = ? and isDeleted='N') exist;";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $bool = $st->fetchAll()[0]['exist'];
    if(!$bool){
        return array(false, "장바구니에 제품을 추가해주세요.",426);
    }
    $query = "select ifnull(address,'주소를 입력해주세요.') as address from User where userIdx=?";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = new stdClass();
    $res->address = $st->fetchAll()[0]['address'];
    $query="select optionName, quantity, needCount from Basket
inner join (select optionIdx,quantity from Stock) as P1
on P1.optionIdx=Basket.optionIdx
inner join (select optionIdx, optionName from ProductOption) as P2
on P2.optionIdx=Basket.optionIdx
where userIdx=?";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    for($i=0;$i<count($result);$i++){
        if($result[$i]['quantity']==0){
            return array(false, strval($result[$i]['optionName'])."는 품절된 제품입니다. 장바구니에서 제거 후 다시 주문해주세요.",424);

        }
        if($result[$i]['quantity']<$result[$i]['needCount']){
            return array(false, strval($result[$i]['optionName'])."는 ".strval($result[$i]['quantity'])."개 까지만 주문할 수 있습니다. 수량 조정 후 다시 주문해주세요",425);
        }
    }
    $query="select Basket.productIdx,Basket.optionIdx,productName, optionName, pictureUrl as productImg,needCount  as optionCount from Basket
inner join (select productIdx, productName, packingType from Product) as P1
on Basket.productIdx=P1.productIdx
left outer join (select productIdx, pictureUrl from ProductPic where pictureKind='main') as P2
on Basket.productIdx= P2.productIdx
left outer join (select productIdx, optionIdx, optionName, originalPrice, clientPrice from ProductOption) as P3
on Basket.optionIdx= P3.optionIdx
left outer join (select optionIdx,quantity from Stock) as P4
on Basket.optionIdx=P4.quantity
where Basket.isDeleted='N' and userIdx=?";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res->orderInfo=$st->fetchAll();

    $query="select userIdx, name as userName, case length(phoneNumber)
       WHEN 11 THEN CONCAT(LEFT(phoneNumber, 3), '-', MID(phoneNumber, 4, 4), '-', RIGHT(phoneNumber, 4))
       WHEN 10 THEN CONCAT(LEFT(phoneNumber, 3), '-', MID(phoneNumber, 4, 3), '-', RIGHT(phoneNumber, 4))
        end as phoneNumber
        from User where userIdx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res->userInfo=$st->fetchAll()[0];
    $query = "select count(*) as allCouponCount from UserCoupon where userIdx=? and isUsed='N' and isDeleted='N'";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $res->couponAndProfit['allCouponCount']=$st->fetchAll()[0]['allCouponCount'];;
    $query = "select count(*) as availableCount from (
select couponName, contents, userIdx,UserCoupon.couponIdx as couponIdx, ifnull(sum(needCount),(select sum(needCount) as needCount from Basket where userIdx=? and Basket.isDeleted='N'))  as needCount,
       ifnull(sum(productPrice),(select sum(P1.clientPrice*Basket.needCount) as productPrice from Basket
           inner join (select optionIdx,clientPrice from ProductOption) as P1
on Basket.optionIdx=P1.optionIdx where userIdx=? and Basket.isDeleted='N')) as productPrice,discountPercent,discountPrice,minPrice,maxDiscount,expiration from UserCoupon
left outer join (select couponIdx , couponName, discountPercent, discountPrice, contents, minCount, minPrice, maxDiscount, expiration from Coupon) as P1
on P1.couponIdx=UserCoupon.couponIdx
left outer join (select couponIdx,productIdx from CouponProduct) as P2
on P2.couponIdx=UserCoupon.couponIdx
left outer join (select productIdx,count(needCount) as needCount from Basket group by productIdx) as P3
on P3.productIdx=P2.productIdx
left outer join (select productIdx, sum(P5.clientPrice*Basket.needCount) as productPrice from Basket inner join (select optionIdx,clientPrice from ProductOption) as P5
on Basket.optionIdx=P5.optionIdx
group by productIdx) as P4
on P2.productIdx=P4.productIdx
where userIdx=? and UserCoupon.isUsed='N' and UserCoupon.isDeleted='N' and timestampdiff(minute, expiration, now())<0 and (isnull(P2.productIdx) or P2.productIdx in (select productIdx from Basket where userIdx=?))
group by UserCoupon.couponIdx,minPrice,minCount
having if(minPrice=0, needCount>=minCount,productPrice>=minPrice)) as P;";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$userIdx,$userIdx,$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res->couponAndProfit['availableCouponCount']=$st->fetchAll()[0]['availableCount'];
    $query="select (select ifnull(sum(point),0) from Point where userIdx=? and isPaid='N')
                        -(select ifnull(sum(point),0) from Point where userIdx=? and isPaid='Y') as point from Point";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res->couponAndProfit['availablePoint']=$st->fetchAll()[0]['point'];
    $query="select ifnull(sum(originalPrice*needCount),0) as totalPrice, ifnull((sum(originalPrice*needCount)-sum(clientPrice*needCount)),0) as salePrice, ifnull(if(sum(clientPrice*needCount)<40000,sum(clientPrice*needCount)+3000,sum(clientPrice*needCount)),0) as priceToPay, ifnull(cast(sum(clientPrice*needCount)*profit/100 as signed integer),0) as profitPrice, profit as profitPercent, if(sum(clientPrice*needCount)>=40000,0,3000) as delivery from Basket
left outer join (select optionIdx, originalPrice, clientPrice from ProductOption) as P3
on Basket.optionIdx= P3.optionIdx
left outer join (select userIdx,level from User) as U
on Basket.userIdx = U.userIdx
left outer join (select level,profit from Profit) as P
on U.level=P.level
where Basket.userIdx=? and Basket.isDeleted='N'";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res->price = $st->fetchAll()[0];
    return array(True, "주문서입니다.",$res);
}
function getBasket($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select ifnull(address,'주소를 입력해주세요.') as address from User where userIdx=?";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = new stdClass();
    $res->address = $st->fetchAll()[0]['address'];
    $query = "select Basket.productIdx, Basket.optionIdx, productName, optionName, pictureUrl as productImg,needCount  as optionCount, originalPrice, clientPrice, left(P1.packingType,2) as type from Basket
inner join (select productIdx, productName, packingType from Product) as P1
on Basket.productIdx=P1.productIdx
left outer join (select productIdx, pictureUrl from ProductPic where pictureKind='main') as P2
on Basket.productIdx= P2.productIdx
left outer join (select productIdx, optionIdx, optionName, originalPrice, clientPrice from ProductOption) as P3
on Basket.optionIdx= P3.optionIdx
where Basket.isDeleted='N' and userIdx=?";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result = $st->fetchAll();
    $res->frozenProduct=array();
    $res->coldProduct=array();
    $res->normalProduct=array();
    for($i=0;$i<count($result);$i++){
        if($result[$i]['type']=='냉동'){
            array_push($res->frozenProduct,$result[$i]);
        }
        else if($result[$i]['type']=='냉장'){
            array_push($res->coldProduct,$result[$i]);
        }
        else{
            array_push($res->normalProduct,$result[$i]);
        }
    }
    $query="select ifnull(sum(originalPrice*needCount),0) as totalPrice, ifnull((sum(originalPrice*needCount)-sum(clientPrice*needCount)),0) as salePrice, ifnull(if(sum(clientPrice*needCount)<40000,sum(clientPrice*needCount)+3000,sum(clientPrice*needCount)),0) as priceToPay, ifnull(cast(sum(clientPrice*needCount)*profit/100 as signed integer),0) as profitPrice, if(sum(clientPrice*needCount)>=40000,0,3000) as delivery from Basket
left outer join (select optionIdx, originalPrice, clientPrice from ProductOption) as P3
on Basket.optionIdx= P3.optionIdx
left outer join (select userIdx,level from User) as U
on Basket.userIdx = U.userIdx
left outer join (select level,profit from Profit) as P
on U.level=P.level
where Basket.userIdx=? and Basket.isDeleted='N'";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res->price = $st->fetchAll()[0];
    return $res;
}


function getSelectPage($userIdx,$productIdx){
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from Product where productIdx = ? and isDeleted='N') exist;";
    $st = $pdo->prepare($query);
    $st->execute([$productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $bool = $st->fetchAll()[0]['exist'];
    if(!$bool){
        return array(false, "존재하지않는제품입니다.",420);
    }
    $res = new stdClass();
    if($userIdx!=null) {
        $query = "select profit from Profit
inner join (select level from User where userIdx=?) as P
on Profit.level=P.level";
        $st = $pdo->prepare($query);
        $st->execute([$userIdx]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res->profit = $st->fetchAll()[0]['profit'];
    }
    $query = "select ProductOption.productIdx, ProductOption.optionIdx, productName, optionName,originalPrice,clientPrice,if(quantity!=0,'N','Y') as isSoldOut from ProductOption
left outer join Stock
on ProductOption.optionIdx = Stock.optionIdx
left outer join (select productIdx, productName from Product) as P
on ProductOption.productIdx=P.productIdx
where ProductOption.productIdx=? and Stock.isDeleted='N'";
    $st = $pdo->prepare($query);
    $st->execute([$productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res->option = $st->fetchAll();
    return array(True, "상품선택페이지입니다.",$res);
}

function getUserInfo($userIdx){
    $pdo = pdoSqlConnect();
    $query ="select distinct name as userName, User.level, ifnull(concat(coupon.couponCount,' 장'),concat(0, '장')) as couponCount, ifnull(basket.basketCount,0) as basketCount, concat(point.point,' 원') as point, profit from User
    left outer join (select userIdx, count(*) as couponCount from UserCoupon where userIdx=? and isUsed='N' and isDeleted='N')
    as coupon
    on coupon.userIdx=User.userIdx
    left outer join(select userIdx, count(*) as basketCount from Basket where userIdx=? and isDeleted='N')
    as basket
    on User.userIdx=basket.userIdx
    left outer join Profit
    on User.level=Profit.level
    inner join (select (select ifnull(sum(point),0) from Point where userIdx=? and isPaid='N')
                        -(select ifnull(sum(point),0) from Point where userIdx=? and isPaid='Y') as point from Point) as point on 1=1
where User.userIdx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$userIdx,$userIdx,$userIdx,$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $result=$st->fetchAll()[0];
    return $result;
}


function getProductInfo($userIdx,$productIdx){

    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from Product where productIdx = ? and isDeleted='N') exist;";
    $st = $pdo->prepare($query);
    $st->execute([$productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $bool = $st->fetchAll()[0]['exist'];
    if(!$bool){
        return array(false, "존재하지않는제품입니다.",420);
    }
    $query = "select count(*) as reviewCount from Review where productIdx=?";
    $st = $pdo->prepare($query);
    $st->execute([$productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res=new stdClass();
    $res->reviewCount = $st->fetchAll()[0]['reviewCount'];
    if($userIdx!=null){
        $query="select User.level, profit, concat(cast(((profit*(select min(clientPrice)/100 from ProductOption where productIdx=?)))as signed integer),'원 적립') as profitPrice from User
inner join Profit
on User.level=Profit.level
where userIdx=?";
        $st = $pdo->prepare($query);
        $st->execute([$productIdx,$userIdx]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res->userInfo=$st->fetchAll()[0];
    }
    $res->productInfo=new stdClass();
    $query = "select Product.productIdx, pictureUrl as mainPic,productName, productComment, PO.originalPrice, PO.clientPrice as clientPrice, PO.salePercent,ifnull(salesUnit,'없음') as salesUnit, ifnull(weight,'없음') as weight, ifnull(shipping,'없음') as shipping, ifnull(origin,'없음') as origin,  ifnull(packingType,'없음') as packingType, ifnull(allergy,'없음') as allergy, ifnull(expiration,'없음') as expiration, ifnull(recordInfo,'없음') as recordInfo, ifnull(guidance,'없음') as guidance, ifnull(calories,'없음') as calories from Product
inner join
(select productIdx,FORMAT(originalPrice,0) as originalPrice,FORMAT(min(clientPrice),0) as clientPrice,case when FORMAT((originalPrice-clientPrice)/originalPrice*100,0) >0 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=5 then 5
    when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>5 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=10 then 10
        when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>10 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=15 then 15
        when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>15 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=20 then 20
        when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>20 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=25 then 25
        when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>25 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=30 then 30
            when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>30 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=35 then 35
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>35 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=40 then 40
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>40 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=45 then 45
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>45 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=50 then 50
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>50 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=55 then 55
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>55 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=60 then 60
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>60 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=65 then 65
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>65 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=70 then 70
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>70 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=75 then 75
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>75 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=80 then 80
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>80 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=85 then 85
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>85 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=90 then 90
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>90 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=95 then 95
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>95 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=100 then 100
                else 0
                    END
         as salePercent from ProductOption group by productIdx) as PO
on PO.productIdx=Product.productIdx
inner join (select productIdx, pictureUrl from ProductPic where pictureKind='main') as pic
on pic.productIdx=Product.productIdx
where Product.productIdx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res->productInfo=$st->fetchAll()[0];
    $query = "select pictureUrl from ProductPic where productIdx=? and pictureKind='explanation' and isDeleted='N'";
    $st = $pdo->prepare($query);
    $st->execute([$productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res->productInfo['explanationPic'] = $st->fetchAll()[0]['pictureUrl'];
    $query = "select pictureUrl from ProductPic where productIdx=? and pictureKind='productImg' and isDeleted='N'";
    $st = $pdo->prepare($query);
    $st->execute([$productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res->productImg = $st->fetchAll()[0]['pictureUrl'];
    $query = "select pictureUrl from ProductPic where productIdx=? and pictureKind='detail' and isDeleted='N'";
    $st = $pdo->prepare($query);
    $st->execute([$productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res->productDetail = $st->fetchAll()[0]['pictureUrl'];
    $query = "select title, isBest, level, replace(name, substr(name, 2,1 ), '*') as userName, if(r.cnt>0,1,0) as isPic, date_format(Review.createdAt,'%Y.%m.%d') as createdAt from Review
inner join User on Review.userIdx = User.userIdx
left outer join (select reviewIdx, count(*) as cnt from ReviewPic group by reviewIdx) as r
on Review.reviewIdx=r.reviewIdx
where Review.productIdx=? and Review.isDeleted='N';
";
    $st = $pdo->prepare($query);
    $st->execute([$productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res->review = $st->fetchAll();
    $query = "select title as inquiryTitle, replace(name, substr(name, 2,1 ), '*') as userName, isLocked, date_format(Inquiry.createdAt,'%Y.%m.%d') as createdAt, if(isnull(A.contents),'답변준비중','답변완료') as isAnswered from Inquiry
inner join User on Inquiry.userIdx = User.userIdx
left outer join Answer A on Inquiry.inquiryIdx = A.inquiryIdx where Inquiry.productIdx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res->inquiry = $st->fetchAll();

    return array(True,"제품정보입니다",$res);
}

function getRecommendPage($userIdx){
    $pdo = pdoSqlConnect();
    $res=new stdClass();
    if($userIdx!=null){
        $query = "select name from User where userIdx=? and isDeleted='N'";
        $st = $pdo->prepare($query);
        $st->execute([$userIdx]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res->userName = $st->fetchAll()[0]['name'];}
    else{
        $res->userName='고객';
    }
    if($userIdx!=null){
        $query = "select count(*) as basketCount from Basket where userIdx=? and isDeleted='N'";
        $st = $pdo->prepare($query);
        $st->execute([$userIdx]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res->basketCount = $st->fetchAll()[0]['basketCount'];}
    else{
        $res->basketCount=0;
    }
    if($userIdx!=null) {
        $query = "select if(count(*)=0,'정육·계란',category) as category from Product
left outer join Basket
on Product.productIdx = Basket.productIdx
where Product.isDeleted='N' and userIdx=?
order by Basket.createdAt
LIMIT 1 ;";
        $st = $pdo->prepare($query);
        $st->execute([$userIdx]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $category = $st->fetchAll()[0]['category'];
    }
    else{
        $category='정육·계란';
    }

    $query ="select Product.productIdx,productName,pictureUrl,PO.originalPrice,concat(PO.clientPrice,'원') as clientPrice,PO.salePercent from Product
inner join
(select productIdx,concat(FORMAT(originalPrice,0),'원') as originalPrice,FORMAT(min(clientPrice),'원') as clientPrice,case when FORMAT((originalPrice-clientPrice)/originalPrice*100,0) >0 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=5 then 5
    when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>5 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=10 then 10
        when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>10 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=15 then 15
        when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>15 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=20 then 20
        when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>20 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=25 then 25
        when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>25 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=30 then 30
            when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>30 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=35 then 35
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>35 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=40 then 40
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>40 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=45 then 45
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>45 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=50 then 50
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>50 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=55 then 55
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>55 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=60 then 60
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>60 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=65 then 65
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>65 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=70 then 70
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>70 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=75 then 75
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>75 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=80 then 80
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>80 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=85 then 85
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>85 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=90 then 90
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>90 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=95 then 95
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>95 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=100 then 100
                else 0
                    END
     as salePercent from ProductOption group by productIdx) as PO
on PO.productIdx=Product.productIdx
inner join (select productIdx, pictureUrl from ProductPic where pictureKind='main') as pic
on pic.productIdx=PO.productIdx
inner join (select *
from(
	select
		productIdx,quantity
	from Stock
	inner join ProductOption PO on Stock.optionIdx = PO.optionIdx where (productIdx, quantity)  in (
		select productIdx, max(quantity)
		from Stock inner join ProductOption PO on Stock.optionIdx = PO.optionIdx group by productIdx
	)
	order by quantity desc
) S group by productIdx) as S1
on S1.productIdx = PO.productIdx
where Product.isDeleted='N' and quantity !=0 and Product.category=?
LIMIT 0,5;";
    $st = $pdo->prepare($query);
    $st->execute([$category]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res->related = $st->fetchAll();
    $query="select Product.productIdx,productName,pictureUrl,PO.originalPrice,concat(PO.clientPrice,'원') as clientPrice,PO.salePercent from Product
inner join
(select productIdx,concat(FORMAT(originalPrice,0),'원') as originalPrice,FORMAT(min(clientPrice),'원') as clientPrice,case when FORMAT((originalPrice-clientPrice)/originalPrice*100,0) >0 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=5 then 5
    when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>5 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=10 then 10
        when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>10 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=15 then 15
        when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>15 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=20 then 20
        when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>20 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=25 then 25
        when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>25 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=30 then 30
            when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>30 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=35 then 35
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>35 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=40 then 40
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>40 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=45 then 45
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>45 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=50 then 50
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>50 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=55 then 55
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>55 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=60 then 60
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>60 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=65 then 65
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>65 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=70 then 70
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>70 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=75 then 75
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>75 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=80 then 80
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>80 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=85 then 85
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>85 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=90 then 90
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>90 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=95 then 95
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>95 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=100 then 100
                else 0
                    END
         as salePercent from ProductOption group by productIdx) as PO
on PO.productIdx=Product.productIdx
inner join (select productIdx, pictureUrl from ProductPic where pictureKind='main') as pic
on pic.productIdx=PO.productIdx
inner join (select *
from(
	select
		productIdx,quantity
	from Stock
	inner join ProductOption PO on Stock.optionIdx = PO.optionIdx where (productIdx, quantity)  in (
		select productIdx, max(quantity)
		from Stock inner join ProductOption PO on Stock.optionIdx = PO.optionIdx group by productIdx
	)
	order by quantity desc
) S group by productIdx) as S1
on S1.productIdx = PO.productIdx
left outer join (select productIdx,count(*) as reviewCount from Review group by productIdx) as R
on R.productIdx=Product.productIdx
where Product.isDeleted='N' and quantity !=0 and Product.category='샐러드·간편식'
order by reviewCount desc
LIMIT 0,5;";

    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $goodComment=$st->fetchAll();
    for($i=0;$i<count($goodComment);$i++){
        $productIdx[$i]=$goodComment[$i]['productIdx'];
    }

    $query="select Review.productIdx,replace(name, substr(name, 2,1 ), '*') as name, title as review from Review
inner join User
on Review.userIdx = User.userIdx
where Review.isBest='Y' and User.isDeleted='N' and Review.isDeleted='N' and FIND_IN_SET(Review.productIdx,:array)";
    $st = $pdo->prepare($query);
    $ids_string=implode(',',$productIdx);
    $st->bindParam(':array',$ids_string);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $bestComment=$st->fetchAll();

    for($j=0;$j<count($goodComment);$j++){
        $goodComment[$j]['bestComment']=array();
    }
    for($i=0;$i<count($bestComment);$i++){
        $productIdx=$bestComment[$i]['productIdx'];
        for($j=0;$j<count($goodComment);$j++){
            if($goodComment[$j]['productIdx']==$productIdx){
            array_push($goodComment[$j]['bestComment'],$bestComment[$i]);
            }
        }
    }
    $res->goodComment = $goodComment;

//    $res->bestComment = $st->fetchAll();
    return $res;
}
function getHomePage($userIdx){
    $pdo = pdoSqlConnect();
    $res=new stdClass();
    if($userIdx!=null){
    $query = "select count(*) as basketCount from Basket where userIdx=? and isDeleted='N'";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res->basketCount = $st->fetchAll()[0]['basketCount'];}
    else{
        $res->basketCount=0;
    }
    $query = "select Product.productIdx,productName,pictureUrl,PO.originalPrice,concat(PO.clientPrice,'원') as clientPrice,PO.salePercent from Product
inner join
(select productIdx,concat(FORMAT(originalPrice,0),'원') as originalPrice,FORMAT(min(clientPrice),'원') as clientPrice,case when FORMAT((originalPrice-clientPrice)/originalPrice*100,0) >0 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=5 then 5
    when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>5 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=10 then 10
        when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>10 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=15 then 15
        when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>15 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=20 then 20
        when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>20 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=25 then 25
        when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>25 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=30 then 30
            when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>30 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=35 then 35
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>35 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=40 then 40
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>40 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=45 then 45
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>45 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=50 then 50
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>50 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=55 then 55
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>55 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=60 then 60
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>60 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=65 then 65
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>65 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=70 then 70
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>70 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=75 then 75
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>75 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=80 then 80
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>80 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=85 then 85
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>85 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=90 then 90
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>90 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=95 then 95
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>95 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=100 then 100
                else 0
                    END
         as salePercent from ProductOption group by productIdx) as PO
on PO.productIdx=Product.productIdx
inner join (select productIdx, pictureUrl from ProductPic where pictureKind='main') as pic
on pic.productIdx=PO.productIdx
inner join (select *
from(
	select
		productIdx,quantity
	from Stock
	inner join ProductOption PO on Stock.optionIdx = PO.optionIdx where (productIdx, quantity)  in (
		select productIdx, max(quantity)
		from Stock inner join ProductOption PO on Stock.optionIdx = PO.optionIdx group by productIdx
	)
	order by quantity desc
) S group by productIdx) as S1
on S1.productIdx = PO.productIdx
where Product.isDeleted='N' and quantity !=0
order by quantity desc LIMIT 0,5;";
    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res->recommend = $st->fetchAll();
    $query = "select Product.productIdx,productName,pictureUrl,PO.originalPrice,concat(PO.clientPrice,'원') as clientPrice,PO.salePercent from Product
inner join
(select productIdx,concat(FORMAT(originalPrice,0),'원') as originalPrice,FORMAT(min(clientPrice),'원') as clientPrice,case when FORMAT((originalPrice-clientPrice)/originalPrice*100,0) >0 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=5 then 5
    when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>5 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=10 then 10
        when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>10 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=15 then 15
        when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>15 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=20 then 20
        when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>20 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=25 then 25
        when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>25 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=30 then 30
            when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>30 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=35 then 35
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>35 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=40 then 40
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>40 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=45 then 45
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>45 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=50 then 50
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>50 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=55 then 55
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>55 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=60 then 60
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>60 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=65 then 65
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>65 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=70 then 70
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>70 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=75 then 75
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>75 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=80 then 80
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>80 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=85 then 85
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>85 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=90 then 90
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>90 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=95 then 95
                when FORMAT((originalPrice-clientPrice)/originalPrice*100,0)>95 && FORMAT((originalPrice-clientPrice)/originalPrice*100,0)<=100 then 100
                else 0
                    END
         as salePercent from ProductOption group by productIdx) as PO
on PO.productIdx=Product.productIdx
inner join (select productIdx, pictureUrl from ProductPic where pictureKind='main') as pic
on pic.productIdx=PO.productIdx
inner join (select *
from(
	select
		productIdx,quantity
	from Stock
	inner join ProductOption PO on Stock.optionIdx = PO.optionIdx where (productIdx, quantity)  in (
		select productIdx, max(quantity)
		from Stock inner join ProductOption PO on Stock.optionIdx = PO.optionIdx group by productIdx
	)
	order by quantity desc
) S group by productIdx) as S1
on S1.productIdx = PO.productIdx
where Product.isDeleted='N' and quantity !=0
order by salePercent desc
LIMIT 0,5;";
    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res->sale = $st->fetchAll();
    return $res;
}
//validation
function isValidNewUser($userId, $password, $name, $email, $phoneNumber,$address, $recommenderId, $event)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from User where userId = ?) exist;";
    $st = $pdo->prepare($query);
    $st->execute([$userId]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    if($userId==null){
        $st = null;
        $pdo = null;
        return array(false, "아이디를 입력해주세요.",401);
        exit;
    }
    if($res[0]['exist']==1){
        $st = null;
        $pdo = null;
        return array(false, "이미 존재하는 ID 입니다.",402);
        exit;
    }
    $pattern1 = '/^[0-9A-Za-z]{6,20}$/u';
    $pattern2='/[a-zA-Z]/u';
    if(!preg_match($pattern1 ,$userId)){
        $st = null;
        $pdo = null;
        return array(false, "ID는 6자리 이상 영문자 또는 영문자와 숫자 조합만 가능합니다.",403);
        exit;
    }
    if(!preg_match($pattern2 ,$userId)){
        $st = null;
        $pdo = null;
        return array(false, "ID는 영문자를 반드시 포함해야합니다.",404);
        exit;
    }
    if($password==null){
        $st = null;
        $pdo = null;
        return array(false, "비밀번호를 입력해주세요.",405);
        exit;
    }
    $pattern3='/^[0-9A-Za-z!@#$%^&*]{10,}$/';
    $pattern4='/(\d)\\1\\1/';
    $num = preg_match('/[0-9]/u', $password);
    $eng = preg_match('/[a-z]/u', $password);
    $spe = preg_match("/[\!\@\#\$\%\^\&\*]/u",$password);
    if(!preg_match($pattern3,$password)){
        $st = null;
        $pdo = null;
        return array(false, "비밀번호는 영문자 또는 숫자 또는 특수문자 조합으로 10자 이상 입력하세요.",406);
        exit;
    }
    if(preg_match($pattern4,$password)){
        $st = null;
        $pdo = null;
        return array(false, "비밀번호는 동일한 숫자를 3개이상 쓰지마세요.",407);
        exit;
    }
    if(($num==0&&$eng==0)||($num==0&&$spe==0)||($eng==0&&$spe==0)){
        $st = null;
        $pdo = null;
        return array(false, "비밀번호엔 영문자 또는 숫자 또는 특수문자 중 적어도 2가지 조합은 사용해야합니다.",408);
        exit;
    }
    if($name==null){
        $st = null;
        $pdo = null;
        return array(false, "이름을 입력해주세요.",409);
        exit;
    }
    if($email==null){
        $st = null;
        $pdo = null;
        return array(false, "이메일을 입력해주세요.",410);
        exit;
    }
    if(!filter_Var($email, FILTER_VALIDATE_EMAIL)){
        $st = null;
        $pdo = null;
        return array(false, "이메일 주소가 옳지 않습니다",411);
        exit;
    }
    if($phoneNumber==null){
        $st = null;
        $pdo = null;
        return array(false, "휴대폰 번호를 입력해주세요.",412);
        exit;
    }
    if(!preg_match("/^01[0-9]{8,9}$/", $phoneNumber))
    {
        $st = null;
        $pdo = null;
        return array(false, "휴대폰 번호가 옳지 않습니다",413);
        exit;
    }
    if($address==null){
        $st = null;
        $pdo = null;
        return array(false, "주소를 입력해주세요.",414);
        exit;
    }

    if($recommenderId!=null&&$event!=null){
        $st = null;
        $pdo = null;
        return array(false, "추천인과 이벤트 둘 중 하나만 입력가능합니다.",415);
        exit;
    }
    return array(True,"유효한 유저입니다.");

}




//POST
function addBasket($userIdx,$option){


    $pdo = pdoSqlConnect();
    for($i=0;$i<count($option);$i++){
        $query = "select EXISTS(select * from ProductOption where productIdx = ? and optionIdx = ? and isDeleted='N') exist;";
        $st = $pdo->prepare($query);
        $st->execute([$option[$i]->productIdx,$option[$i]->optionIdx]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $bool = $st->fetchAll()[0]['exist'];
        if(!$bool){
            return array(false, "존재하지 않는 제품이 섞여있습니다.",420);
        }
    }

    try{
        $bool2=0;
        $pdo->beginTransaction();
        for($i=0;$i<count($option);$i++){
            $query = "select quantity from Stock where optionIdx=?;";
            $st = $pdo->prepare($query);
            $st->execute([$option[$i]->optionIdx]);
            $st->setFetchMode(PDO::FETCH_ASSOC);
            $bool = $st->fetchAll()[0]['quantity'];
            if(!$bool){
                $pdo->rollBack();
                $query = "select optionName from ProductOption where optionIdx=?;";
                $st = $pdo->prepare($query);
                $st->execute([$option[$i]->optionIdx]);
                $st->setFetchMode(PDO::FETCH_ASSOC);
                return array(false, strval($st->fetchall()[0]['optionName'])."은 품절된 제품입니다. 다시 골라주세요.",421);
            }
            $query = "select EXISTS(select * from Basket where userIdx = ? and optionIdx=? and isDeleted='N') exist;";
            $st = $pdo->prepare($query);
            $st->execute([$userIdx,$option[$i]->optionIdx]);
            $st->setFetchMode(PDO::FETCH_ASSOC);
            $bool = $st->fetchAll()[0]['exist'];
            if($bool==0){
                $query = "insert into Basket (userIdx, productIdx, optionIdx,needCount) values(?,?,?,?);";
                $st = $pdo->prepare($query);
                $st->execute([$userIdx,$option[$i]->productIdx,$option[$i]->optionIdx,$option[$i]->count]);

            }
            else {
                $query = "update Basket set needCount = needCount+? where userIdx=? and optionIdx=?;";
                $st = $pdo->prepare($query);
                $st->execute([$option[$i]->count, $userIdx, $option[$i]->optionIdx]);
                $bool2 = 1;
            }
        }
        $pdo->commit();
        if($bool2==0){
            return array(True,"장바구니에 상품을 담았습니다.",200);
        }
        return array(True,"이미 담으신 상품이 있어 추가되었습니다.",202);

    }
    catch ( PDOException $e ) {
        // Failed to insert the order into the database so we rollback any changes
        $pdo->rollback();
        throw $e;
    }

}


function createUser($userId,$password,$name,$email, $phoneNumber,$address,$birthday,$gender,$recommenderId,$event,$acceptPrivacy,$isSMS,$isEmail)
{
        $pdo = pdoSqlConnect();
        $query = "select ifnull(max(userIdx)+1,1) as userIdx from User;";
        $st = $pdo->prepare($query);
        $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $userIdx = $st->fetchAll()[0]['userIdx'];
        $pdo = pdoSqlConnect();
        $query = "INSERT INTO User (userIdx,userId,password,name,email, phoneNumber,address,birthday,gender,recommenderId,event,acceptPrivacy,isSMS,isEmail) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?);";
        $st = $pdo->prepare($query);
        $st->execute([$userIdx,$userId,$password,$name,$email, $phoneNumber,$address,$birthday,$gender,$recommenderId,$event,$acceptPrivacy,$isSMS,$isEmail]);
        $st = null;
        $pdo = null;
        return $userIdx;
}


