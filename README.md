Тестовое задание

+ 1 задание
```sql
select u_t.id, concat(u_t.first_name, ' ', u_t.last_name) as name, b_t.author, group_concat(b_t.name) as books
from user_books ub_t
         join books b_t on ub_t.book_id = b_t.id
         join users u_t on ub_t.user_id = u_t.id
where TIMESTAMPDIFF(YEAR, u_t.birthday, curdate()) between 7 and 17
and TIMESTAMPDIFF(DAY, ub_t.get_date, ub_t.return_date) <= 14
group by u_t.id, b_t.author
having COUNT(b_t.author) = 2;
```

+ Развертывание 2 задания
  + Клонирование проекта
  + ```composer install```
  + ```docker compose up -d```
  + Ключ API доступен в конфиге auth (```config/auth.php[apikey]```)
  + Можно пользоваться 
    + Обращение к API по адресу http://localhost/api/v1?metho=method_name&....
  
