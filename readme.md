# docker简介及建议

- 1.docker是golang开发的一套能够把开发的应用程序自动部署到容器的面向接口的开源引擎
- 2.快速部署、快速迁移、快速搭建环境、部署服务
- 3.镜像、容器、服务三要素
- 4.运维人员必会<部署>、开发人员必学<使用>
- 5.其他介绍自行搜索

# 不懂docker怎么办
相信大家都了解并会使用简单的docker，其实开发人员至少学会简单的使用，个人使用最频繁的指令可以分享给大家
- 1.pull 当你想使用别人的镜像的时候你怎么用，得去下载下来吧，所以第一步就是pull<拉>
- 2.run 当你下载下来人家的镜像你得用起来吧，那就run<执行>,此时，你的pull下来的镜像会变成一个服务，也叫容器
- 3.commit  当你run的挺爽的时候你觉得这不是你的镜像，你想占为己有怎么办？那就<commit>提交吧
- 4.push 提交完了呢，就会变成自己的镜像么？当然不是，用过git的人都知道commit到缓存区后得push到仓库内部去吧，当然，这波操作你得登录hub.docker啊，不用担心，注册就和你平常在贴吧求种没账号注册一样简单
- 5.kill 卧槽一激动run了一堆容器出来咋办？kill掉，没用的就rm掉，镜像也一样，只不过是rmi而已
是不是很简单很有意思呢？抱歉，我不想打击你，你才学会了hello world

# 这个文档的意图是什么
上正菜了哈
使用docker部署mysql集群，并使用haproxy做数据库的负载均衡
- 1.什么是数据库的集群？
两台+数据库机器共同维护一套完整的逻辑数据体系，每台当做一个数据库节点，更新数据后会同步或异步的把其他节点数据库给统一的一个方案
- 2.数据同步方案是什么
通常是有两种方案，1.Replication   2.PXC,其中Replication是异步同步数据，因此数据弱一致性，但速度快，不能保证所有节点数据完全一致，PXC就不一样了，是同步同步数据《字没打错哈，自己品味》，数据强一致性，但速度慢，因为数据更新要同步
其他节点后才返回是否成功更新，因此效率不算快的。
- 3.什么是数据库的负载均衡
相信大家都知道nginx的负载均衡，突然来了10000个请求咋整，一台服务器受不了啊，那么就分发到多几台服务器共同分担压力，有点凑钱交房贷的意思。
那么数据库的负载均衡是一个道理，一堆数据库请求下来，分发到不同数据库服务器一起分担压力嘛。
我们这里使用的是haproxy开源软件，提供高可用的负载均衡

#实战搭建
上大闸蟹
环境：新鲜出炉的centos7.3系统，干净、绿色、无污染。
接下来我会一个不漏的命令和讲解一步步走到底，让没缓过神的童鞋能有更好的理解，相信我，第一次搭建成功你会很有成就感的。

- 1.docker的安装
其实我很不愿意带小白走这一步的，为了部落，忍忍吧
1.yum update 更新yum软件源
2.yum install -y docker 安装docker  centos就是这么吊，自带docker软件源
3.service docker start 启动docker服务
- 2.镜像和容器的配置以及部署
1.docker pull percona/percona-xtradb-cluster  拉取pxc服务镜像
2.docker network create net1  为了安全考虑创建内部网络，可自定义网段，不过不使用这个问题也不大
3.docker volume create --name v1 
    docker volume create --name v2
        docker volume create --name v3
            docker volume create --name v4
                docker volume create --name v5
  创建5个数据卷，数据卷是什么呢？就是容器到宿主机之间的share files，为什么要创建5个呢？因为咱们这次要挂载5个节点数据库集群
4.阿里云开启安全组端口3333/3340 开放端口链接mysql测试
5.docker run -d -p 3333:3306 -v v1:/var/lib/mysql -e MYSQL_ROOT_PASSWORD=zhicongdai -e CLUSTER_NAME=PXC -e XTRABACKUP_PASSWORD=zhicongdai --privileged --name=node1 --net=net1 pxc 创建主节点数据库
6.docker run -d -p 3337:3306 -v v2:/var/lib/mysql -e MYSQL_ROOT_PASSWORD=zhicongdai -e CLUSTER_NAME=PXC -e XTRABACKUP_PASSWORD=zhicongdai -e CLUSTER_JOIN=node1  --privileged --name=node2 --net=net1 pxc
7.docker run -d -p 3336:3306 -v v3:/var/lib/mysql -e MYSQL_ROOT_PASSWORD=zhicongdai -e CLUSTER_NAME=PXC -e XTRABACKUP_PASSWORD=zhicongdai -e CLUSTER_JOIN=node1  --privileged --name=node3 --net=net1 pxc
8.docker run -d -p 3335:3306 -v v4:/var/lib/mysql -e MYSQL_ROOT_PASSWORD=zhicongdai -e CLUSTER_NAME=PXC -e XTRABACKUP_PASSWORD=zhicongdai -e CLUSTER_JOIN=node1  --privileged --name=node4 --net=net1 pxc
9.docker run -d -p 3335:3306 -v v5:/var/lib/mysql -e MYSQL_ROOT_PASSWORD=zhicongdai -e CLUSTER_NAME=PXC -e XTRABACKUP_PASSWORD=zhicongdai -e CLUSTER_JOIN=node1  --privileged --name=node5 --net=net1 pxc
我这里写5个节点   实际上我在阿里云成功部署了2个节点    此时内存使用率75%,再加一个节点就爆了，已经重启了一波服务器了，大家知道这个是就行了，本地测试没问题
10.验证：用客户端连接远程mysql，修改其中一台的库、表等信息另外所有节点全部自动同步，是不是很兴奋呢？别着急，兴奋的在后面。好了数据库的集群咱们已经做好了。

- 3.数据库的负载均衡
1.完成了数据库的集群有什么用呢，目的是数据容灾、流量分担对不对，咱们集群也做了，也能做到容灾了，那么流量分担谁来做呢？如果不分担的话，做集群的意义何在，那么咱们就会想到能不能向nginx分发请求一样分发数据库连接呢，答案是可以的,haproxy能很好的做到这点。
2.haproxy是什么?为什么要使用haproxy呢？首先haproxy官方介绍我就不多说了，至于为什么要使用这个软件做负载均衡，原因很简单：免费、性能好、支持虚拟机，因为咱们是docker部署，docker其实就是台虚拟机来的。
3.安装haproxy镜像。到这步了大家就差不多猜到怎么做了，我给你一个haproxy地址，你pull下来就是了，没错，就是这么简单 你已经入门了，docker pull haproxy
4.创建并配置haproxy配置文件。这个就麻烦了，由于这是个配置文件，里面的东西是可以按需修改的，你不能写死在docker容器里面吧，对不对，难道要修改配置的话，我还得exec container、vi haproxy.cfg一顿操作修改么？当然不是，记得我们之前说过的数据卷么？没错
你跟上我了，把配置文件用数据卷拉出至共享宿主机（别问我什么是宿主机），然后修改同时生效，岂不美哉？是的，大家举一反三一下，是不是其他的服务配置文件都可以share呢，嗯，你很有才华。
mkdir -p /var/haproxy 创建文件夹   vi /var/haproxy/haproxy.cfg 配置文件的编辑，这块内容贼多，我会以附件的形式上传，网上一大堆，要改的也不多，需要注意的是,新建一个node1账号用作心跳包检测CREATE USER 'haproxy'@'%' IDENTIFIED BY '';并把之前的集群ip写进去，不知道可以执行docker inspect node1去看
5.docker run -it -d -p 4001:8888 -p 4002:3306 -v /var/haproxy:/usr/local/etc/haproxy --name haproxy --privileged --net=net1 haproxy 开启负载均衡服务容器
6.docker exec -it haproxy /bin/bash 进入容器，你说干啥呢？当然是启动服务啦
7.haproxy -f /usr/local/etc/haproxy/haproxy.cfg  这就开启了
8.exit 退出容器
9.见证奇迹的时刻，请在浏览器输入宿主机域名:4001/dbs，会有意外收获
10.恭喜你，成功使用docker搭建了一个带负载均衡的数据库集群

