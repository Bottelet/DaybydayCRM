-- Permission milalao base
insert into permissions(name,external_id,display_name,description, grouping) values ('manipulate-db','','Manipulate the database','Be able to manipulate the database','db');

create table test_import(
chaine varchar(255),
chiffre decimal(12,2),
daty date
);
