<?php


//READ
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
function getRecommendPage($userIdx){
    $pdo = pdoSqlConnect();
    $res=new stdClass();
    if($userIdx!=null){
        $query = "select name from User where userIdx=? and isDeleted='N'";
        $st = $pdo->prepare($query);
        $st->execute([$userIdx]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res->userName = $st->fetchAll();}
    else{
        $res->userName[0]['basketCount']='고객';
    }
    if($userIdx!=null){
        $query = "select count(*) as basketCount from Basket where userIdx=? and isDeleted='N'";
        $st = $pdo->prepare($query);
        $st->execute([$userIdx]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res->basketCount = $st->fetchAll();}
    else{
        $res->basketCount[0]['basketCount']=0;
    }
    $category="";
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
//    $query = "select category from Product
//inner join Basket
//on Product.productIdx = Basket.productIdx
//where Product.isDeleted='N' and userIdx=?
//order by Basket.createdAt
//LIMIT 1 ;";
//    $st = $pdo->prepare($query);
//    $st->execute([$userIdx]);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $category=$st->fetchAll();
    $query ="select Product.productIdx,productName,pictureUrl,PO.originalPrice,concat(PO.clientPrice,'원') as clientPrice,PO.salePercent from Product
inner join
(select productIdx,concat(FORMAT(originalPrice,0),'원') as originalPrice,FORMAT(min(clientPrice),'원') as clientPrice,concat(case when FORMAT((originalPrice-clientPrice)/clientPrice*100,0) >0 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=5 then 5
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
        ,'%') as salePercent from ProductOption group by productIdx) as PO
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
(select productIdx,concat(FORMAT(originalPrice,0),'원') as originalPrice,FORMAT(min(clientPrice),'원') as clientPrice,concat(case when FORMAT((originalPrice-clientPrice)/clientPrice*100,0) >0 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=5 then 5
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
        ,'%') as salePercent from ProductOption group by productIdx) as PO
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
    $res->basketCount = $st->fetchAll();}
    else{
        $res->basketCount[0]['basketCount']=0;
    }
    $query = "select Product.productIdx,productName,pictureUrl,PO.originalPrice,concat(PO.clientPrice,'원') as clientPrice,PO.salePercent from Product
inner join
(select productIdx,concat(FORMAT(originalPrice,0),'원') as originalPrice,FORMAT(min(clientPrice),'원') as clientPrice,concat(case when FORMAT((originalPrice-clientPrice)/clientPrice*100,0) >0 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=5 then 5
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
        ,'%') as salePercent from ProductOption group by productIdx) as PO
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
(select productIdx,concat(FORMAT(originalPrice,0),'원') as originalPrice,FORMAT(min(clientPrice),'원') as clientPrice,concat(case when FORMAT((originalPrice-clientPrice)/clientPrice*100,0) >0 && FORMAT((originalPrice-clientPrice)/clientPrice*100,0)<=5 then 5
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
        ,'%') as salePercent from ProductOption group by productIdx) as PO
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


