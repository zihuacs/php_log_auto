<?php
/*
 * Created on 2012-10-23
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 $sendStr="Bob喜欢玩电脑游戏，特别是战略游戏。但是他经常无法找到快速玩过游戏的办法。现在他有个问题。他要建立一个古城堡，城堡中的路形成一棵树，路即树的边。他要在这棵树的结点上放置最少数目的士兵，使得这些士兵能了望到所有的路。注意，某个士兵在一个结点上时，与该结点相连的所有边将都可以被了望到。请你编一程序，给定一树，帮Bob计算出他需要放置最少的士兵";
 $socket=socket_create(AF_INET,SOCK_STREAM,getprotobyname("tcp"));
 
 if(socket_connect($socket,"localhost",13999)){
  
  socket_write($socket,$sendStr,strlen($sendStr));
  
  $receiveStr="";

  $receiveStr=socket_read($socket,1024*1024);
  echo "client:".$receiveStr;  
 }
 socket_close($socket);
?>
