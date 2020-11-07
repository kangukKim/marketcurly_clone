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


//GET
function getProductInfo($productIdx){
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
    $res->productInfo=new stdClass();
    $query = "select Product.productIdx, pictureUrl as mainPic,productName, productComment, PO.originalPrice, concat(PO.clientPrice,'원') as clientPrice, PO.salePercent,ifnull(salesUnit,'없음') as salesUnit, ifnull(weight,'없음') as weight, ifnull(shipping,'없음') as shipping, ifnull(origin,'없음') as origin,  ifnull(packingType,'없음') as packingType, ifnull(allergy,'없음') as allergy, ifnull(expiration,'없음') as expiration, ifnull(recordInfo,'없음') as recordInfo, ifnull(guidance,'없음') as guidance, ifnull(calories,'없음') as calories from Product
inner join
(select productIdx,concat(FORMAT(originalPrice,0),'원') as originalPrice,FORMAT(min(clientPrice),'원') as clientPrice,case when FORMAT((originalPrice-clientPrice)/clientPrice*100,0) >0 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=5 then 5
    when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>5 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=10 then 10
        when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>10 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=15 then 15
        when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>15 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=20 then 20
        when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>20 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=25 then 25
        when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>25 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=30 then 30
            when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>30 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=35 then 35
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>35 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=40 then 40
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>40 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=45 then 45
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>45 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=50 then 50
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>50 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=55 then 55
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>55 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=60 then 60
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>60 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=65 then 65
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>65 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=70 then 70
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>70 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=75 then 75
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>75 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=80 then 80
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>80 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=85 then 85
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>85 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=90 then 90
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>90 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=95 then 95
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>95 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=100 then 100
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
    $query = "select title as inquiryTitle, replace(name, substr(name, 2,1 ), '*') as userName, isLocked, date_format(Inquiry.createdAt,'%Y.%m.%d') as createdAt, if(A.contents!=null,'답변완료','답변준비중') as isAnswered from Inquiry
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
        $query = "select category from Product
inner join Basket
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
(select productIdx,concat(FORMAT(originalPrice,0),'원') as originalPrice,FORMAT(min(clientPrice),'원') as clientPrice,case when FORMAT((originalPrice-clientPrice)/clientPrice*100,0) >0 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=5 then 5
    when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>5 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=10 then 10
        when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>10 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=15 then 15
        when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>15 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=20 then 20
        when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>20 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=25 then 25
        when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>25 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=30 then 30
            when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>30 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=35 then 35
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>35 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=40 then 40
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>40 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=45 then 45
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>45 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=50 then 50
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>50 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=55 then 55
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>55 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=60 then 60
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>60 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=65 then 65
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>65 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=70 then 70
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>70 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=75 then 75
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>75 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=80 then 80
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>80 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=85 then 85
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>85 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=90 then 90
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>90 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=95 then 95
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>95 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=100 then 100
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
(select productIdx,concat(FORMAT(originalPrice,0),'원') as originalPrice,FORMAT(min(clientPrice),'원') as clientPrice,case when FORMAT((originalPrice-clientPrice)/clientPrice*100,0) >0 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=5 then 5
    when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>5 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=10 then 10
        when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>10 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=15 then 15
        when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>15 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=20 then 20
        when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>20 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=25 then 25
        when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>25 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=30 then 30
            when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>30 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=35 then 35
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>35 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=40 then 40
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>40 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=45 then 45
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>45 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=50 then 50
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>50 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=55 then 55
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>55 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=60 then 60
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>60 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=65 then 65
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>65 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=70 then 70
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>70 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=75 then 75
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>75 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=80 then 80
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>80 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=85 then 85
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>85 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=90 then 90
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>90 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=95 then 95
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>95 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=100 then 100
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

    $res->goodComment = $goodComment;
    $query="select Review.productIdx,replace(name, substr(name, 2,1 ), '*') as name, title as review from Review
inner join User
on Review.userIdx = User.userIdx
where Review.isBest='Y' and User.isDeleted='N' and Review.isDeleted='N' and FIND_IN_SET(Review.productIdx,:array)";
    $st = $pdo->prepare($query);
    $ids_string=implode(',',$productIdx);
    $st->bindParam(':array',$ids_string);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res->bestComment = $st->fetchAll();
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
(select productIdx,concat(FORMAT(originalPrice,0),'원') as originalPrice,FORMAT(min(clientPrice),'원') as clientPrice,case when FORMAT((originalPrice-clientPrice)/clientPrice*100,0) >0 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=5 then 5
    when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>5 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=10 then 10
        when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>10 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=15 then 15
        when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>15 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=20 then 20
        when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>20 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=25 then 25
        when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>25 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=30 then 30
            when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>30 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=35 then 35
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>35 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=40 then 40
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>40 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=45 then 45
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>45 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=50 then 50
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>50 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=55 then 55
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>55 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=60 then 60
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>60 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=65 then 65
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>65 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=70 then 70
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>70 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=75 then 75
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>75 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=80 then 80
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>80 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=85 then 85
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>85 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=90 then 90
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>90 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=95 then 95
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>95 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=100 then 100
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
(select productIdx,concat(FORMAT(originalPrice,0),'원') as originalPrice,FORMAT(min(clientPrice),'원') as clientPrice,case when FORMAT((originalPrice-clientPrice)/clientPrice*100,0) >0 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=5 then 5
    when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>5 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=10 then 10
        when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>10 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=15 then 15
        when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>15 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=20 then 20
        when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>20 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=25 then 25
        when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>25 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=30 then 30
            when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>30 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=35 then 35
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>35 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=40 then 40
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>40 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=45 then 45
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>45 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=50 then 50
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>50 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=55 then 55
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>55 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=60 then 60
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>60 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=65 then 65
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>65 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=70 then 70
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>70 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=75 then 75
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>75 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=80 then 80
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>80 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=85 then 85
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>85 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=90 then 90
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>90 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=95 then 95
                when FORMAT((originalPrice-clientPrice)/clientPrice*100,0)>95 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=100 then 100
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
function addBasket($userIdx,$productIdx,$optionIdx,$count){

    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from ProductOption where productIdx = ? and optionIdx = ? and isDeleted='N') exist;";
    $st = $pdo->prepare($query);
    $st->execute([$productIdx,$optionIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $bool = $st->fetchAll()[0]['exist'];
    if(!$bool){
        return array(false, "존재하지 않는 제품입니다.",420);
    }
    $query = "select quantity from Stock where optionIdx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$optionIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $bool = $st->fetchAll()[0]['quantity'];
    if(!$bool){
        return array(false, "품절된 제품입니다.",421);
    }
    $query = "select EXISTS(select * from Basket where userIdx = ? and optionIdx=? and isDeleted='N') exist;";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$optionIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $bool = $st->fetchAll()[0]['exist'];
    if($bool==0){
        $query = "insert into Basket (userIdx, productIdx, optionIdx,needCount) values(?,?,?,?);";
        $st = $pdo->prepare($query);
        $st->execute([$userIdx,$productIdx,$optionIdx,$count]);
        return array(True,"장바구니에 상품을 담았습니다.",201);
    }
    $query = "update Basket set needCount = needCount+? where userIdx=? and optionIdx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$count,$userIdx,$optionIdx]);
    return array(True,"장바구니에 상품을 담았습니다. 이미 담으신 상품이 있어 추가되었습니다.",202);

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


