create database youtubePlaylists;	
use youtubePlaylists;

create table playlists(
	id int primary key auto_increment,
    playlist_id varchar(50),
    title varchar(250),
    thumbnail_url varchar(120),
	published_at timestamp default current_timestamp,
    etag varchar(40) -- check if there was any update (add/delete video)
);

create table videos(
	id int primary key auto_increment,
    video_id varchar(120),
    title varchar(250),
    thumbnail_url varchar(120),
	published_at timestamp default current_timestamp,
	etag varchar(40), -- check if there was any change on a video by the loading moment
    idPlaylist int,
    constraint fk_idPlaylist foreign key(idPlaylist) references playlists(id)
);

-- drop table videos;
-- select * from videos;
-- truncate playlists;

create procedure insert_playlist(parmPlayId varchar(50), parmTitle varchar(250), parmThumbUrl varchar(120), parmPublishedAt timestamp, parmEtag varchar(40))
	insert into playlists(id, playlist_id, title, thumbnail_url, published_at, etag) values(NULL, parmPlayId, parmTitle, parmThumbUrl, parmPublishedAt, parmEtag);
-- drop procedure insert_playlist;

# FALTA TESTAR
create procedure update_Playlist(parmId int, parmPlayId varchar(50), parmTitle varchar(250), parmThumbUrl varchar(120), parmPublishedAt timestamp, parmEtag varchar(40))
	update playlists set title = parmTitle, thumbnail_url = parmThumbUrl, published_at = parmPublishedAt, etag = parmEtag where id = parmId and playlist_id = parmPlayId;
	    
-- desc videos;    
create procedure insert_video(parmVideoId varchar(120), parmTItle varchar(250), parmThumbUrl varchar(120), parmPublishedAt timestamp, parmEtag varchar(40), parmIdPlaylist int) 
	insert into videos(id, video_id, title, thumbnail_url, published_at, etag, idPlaylist) values(NULL, parmVideoId, parmTitle, parmThumbUrl, parmPublishedAt, parmEtag, parmIdPlaylist);
    
DELIMITER $$
create procedure search_idVideos_by_playlist(parmPlayID varchar(50))
BEGIN
	DECLARE id_num int default 0;
    SELECT id from playlists where playlist_id = parmPlayID into id_num;
    SELECT video_id, id_num as 'id_numerico' from videos where idPlaylist = id_num;
END ; $$
DELIMITER ;   

drop procedure search_idVideos_by_playlist;
call search_idVideos_by_playlist('PL2hQMuIBSUKfanhhpBCee8y7L9CVWMhJy');
update playlists set etag = 'a' where id = 1 or id = 2 or id = 17 or id = 20 or id = 22 or id = 27 or id = 30 or id = 31 or id = 33 or id = 34 or id = 37;
update playlists set etag = 'a';

call insert_video('UEwyaFFNdUlCU1VLZFY0ODVCMmtHMUI2aWFlRlF2R1pGXy45QkE2RUQ2NTEzNzY5NDEz','NERVO feat. Dev Kreayshawn Alisa - Hey Ricky (FTampa Remix) | Music Visualization🖤🎶💎','https://i.ytimg.com/vi/Gaxi3OW8PKU/sddefault.jpg','2021-01-03 04:05:13 ','dZM7EfIpZmcG_Q2vVfR-1kw85Xo','23'); 
    
     
select id,title from playlists where title = 'Background music';
select distinct idPlaylist from videos order by idPlaylist;
select * from videos;
select * from playlists;
delete from playlists;
select count(id) from videos where idPlaylist = 23; 
/*select distinct title from videos;
truncate videos;    
call insert_playlist();    
select * from playlists where id = 2;
select * from playlists;
select * from videos where idPlaylist = 23;

-- Para deletar registros de duas tabelas pode ser feito dessa forma:
delete from playlists where id = 38;
delete from videos where idPlaylist = 38;
-- Ou dessa:
delete from videos using videos inner join playlists on videos.idPlaylist = playlists.id where playlists.id = 38;*/

	 