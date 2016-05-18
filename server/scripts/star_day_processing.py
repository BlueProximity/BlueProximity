import MySQLdb
from datetime import datetime, timedelta
from calendar import monthrange

def build_time_range(start, end):
    # print start.year, start.month
    dates = []
    while start <end:
        # month_end = monthrange(start.year, start.month)[1]
        dates.append((start, start + timedelta(1,0,-1)))
        start += timedelta(1,0)
    return dates


db = MySQLdb.connect("mysql.cefns.nau.edu","blueproximity", "blueprox", "blueproximity")

cursor =db.cursor()

query = "SELECT DISTINCT user_name FROM user_stats_retroactive"

cursor.execute(query)
users = cursor.fetchall()
# for i in k:
#     print i[0]

cursor.execute("DROP TABLE IF EXISTS star_stats_days_retroactive")
query ="CREATE TABLE star_stats_days_retroactive(Id INT PRIMARY KEY AUTO_INCREMENT, enter_incident INT, exit_incident INT, total_use INT, start_date DATETIME, end_date DATETIME, user_name VARCHAR(64))"
cursor.execute(query)
start_clock = datetime(2014, 1,1)
end_clock = datetime(2017, 1,1)
date_list = build_time_range(start_clock, end_clock)
time_format = '%Y-%m-%d %H:%M:%S'

# time_format = '%Y/%m/%d'
# for i in date_list:
#     print i
day = 1
for date in date_list:

    for user in users:
        query = "SELECT COUNT(*) FROM user_stats_retroactive WHERE enter_time BETWEEN '{1}' AND '{2}' AND user_name='{0}' AND hand_sant_enter=0".format(user[0], datetime.strftime(date[0], time_format), datetime.strftime(date[1], time_format))
        cursor.execute(query)
        missed_enter = cursor.fetchone()[0]
        query = "SELECT COUNT(*) FROM user_stats_retroactive WHERE enter_time BETWEEN '{1}' AND '{2}' AND user_name='{0}' AND hand_sant_exit=0".format(user[0], datetime.strftime(date[0], time_format), datetime.strftime(date[1], time_format))
        cursor.execute(query)
        missed_exit = cursor.fetchone()[0]
        query = "SELECT COUNT(*) FROM user_stats_retroactive WHERE enter_time BETWEEN '{1}' AND '{2}' AND user_name='{0}'".format(user[0], datetime.strftime(date[0], time_format), datetime.strftime(date[1], time_format))
        cursor.execute(query)
        total = cursor.fetchone()[0]


        query = "INSERT INTO star_stats_days_retroactive(enter_incident, exit_incident, total_use, start_date, end_date, user_name) VALUES({0}, {1}, {2},'{3}','{4}','{5}')".format(missed_enter, missed_exit, total, datetime.strftime(date[0], time_format), datetime.strftime(date[1], time_format), user[0])
        cursor.execute(query)
        db.commit()

        # print missed_enter, missed_exit, total, datetime.strftime(date[0], time_format), datetime.strftime(date[1], time_format), user[0]
    print day
    day+=1
db.close()
