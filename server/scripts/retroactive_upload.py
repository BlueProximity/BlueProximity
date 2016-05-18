import MySQLdb
db = MySQLdb.connect("mysql.cefns.nau.edu","blueproximity", "blueprox", "blueproximity")

cursor =db.cursor()
file_list = [ "user_stats_retroactive.csv","access_log_retroactive.csv" ] #"user_device_retroactive.csv","beacon_device_retroactive.csv"]
# file_list =["beacon_device_retroactive.csv"]
count = 100
total_Sent = 0
for file_in in file_list:
    csv = open(file_in, "rb")
    selection = 'INSERT INTO {} VALUES '.format(file_in.split(".")[0])
    query = ""
    for row in csv:
        col = row.split(",")
        if (file_in == "access_log_retroactive.csv"):
            count -= 1
            query += '({},{},{}),'.format(col[0],col[1],col[2].split("\r\n")[0])
        elif(file_in=="user_device_retroactive.csv"):

            query += '({},{},{}),'.format(col[0],col[1],col[2].split("\r\n")[0])
        elif(file_in=="user_stats_retroactive.csv"):
            count -= 1
            query += '({},{},{},{},{},{},{}),'.format(col[0],col[1],col[2],col[3],col[4],col[5],col[6].split("\r\n")[0])
        else:
            query += '({},{}),'.format(col[0],col[1].split("\r\n")[0])
        # if(file_in =="access_log_retroactive.csv"):
        if(count <=0 ):
            cursor.execute(selection + query[:-1])
            db.commit()
            query = ""
            # selection = 'INSERT INTO {} VALUES '.format(file_in.split(".")[0])
            total_Sent +=100
            # print total_Sent
            count=100
    # if(file_in!="access_log_retroactive.csv"):
    selection += query[:-1]
    print total_Sent +(100-count)
    total_Sent=0
    # if(file_in!="access_log_retroactive.csv"):
    #     print selection
    try:
        # if(file_in =="access_log_retroactive.csv"):
        #     if(count <=0 ):
        # cursor.execute(selection)
        # db.commit()
        # selection = 'INSERT INTO {} VALUES '.format(file_in.split(".")[0])
        # total_Sent +=100
        # print total_Sent
        # count=100
        # else:
        cursor.execute(selection)
            # print selection
        db.commit()
    except:
        db.rollback()
    csv.close()

db.close
