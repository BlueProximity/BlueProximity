from datetime import datetime, timedelta
from math import floor, ceil
from random import randint, random
from csv import reader
from Queue import PriorityQueue
import numpy as np

roomPool = {}
userPool = {}
prim = 1

users = {}
def decision(prob):
    return random() <= prob

# code from https://www.centos.org/docs/5/html/5.2/Virtualization/sect-Virtualization-Tips_and_tricks-Generating_a_new_unique_MAC_address.html
def randomMAC():
	mac = [ 0x00, 0x16, 0x3e,
		randint(0x00, 0x7f),
		randint(0x00, 0xff),
		randint(0x00, 0xff) ]
	return ''.join(map(lambda x: "%02x" % x, mac))

def retroUsers(filename):
    try:
        with open(filename,"rb") as userDB:
            line = reader(userDB,delimiter=',',quotechar='"')
            for row in line:
                userPool[row[0]]=row[1]+" " + row[2]
                users[row[0]]=row[1]+" " + row[2]
            userDB.close()
        return
    except:
        takenNames= {}
        with open("user_device.csv","rb") as userDB:
            line = reader(userDB,delimiter=',',quotechar='"')
            csvMat=[]
            for row in line:
                tmp = []
                for i in row:
                    tmp.append(i)
                takenNames[row[1] + " " +row[2]] = row[0]
                csvMat.append(tmp)
            userDB.close()
        while(len(takenNames)<20):
            indFirst = randint(0, len(csvMat)-1)
            indLast = randint(0, len(csvMat)-1)
            try:
                dummy = takenNames[csvMat[indFirst][1]+ " " +csvMat[indLast][2]]
            except:
                takenNames[csvMat[indFirst][1]+ " " +csvMat[indLast][2]] = randomMAC()
        names = takenNames.keys()
        writeFile = open(filename, "w")
        for name in names:
            tmp = name.split(" ")
            line = '"{0}","{1}","{2}"\n'.format(takenNames[name], tmp[0],tmp[1])
            writeFile.write(line)
        writeFile.close()
        retroUsers(filename)

def retroDevices(filename):
    try:
        with open(filename,"rb") as deviceDB:
            line = reader(deviceDB,delimiter=',',quotechar='"')
            for row in line:
                roomPool[row[1]]=row[0]
            deviceDB.close()
        return
    except:
        rooms = {}
        with open("beacon_device.csv","rb") as deviceDB:
            line = reader(deviceDB,delimiter=',',quotechar='"')
            for row in line:
                rooms[row[1]]=row[0]
            deviceDB.close()
        writeFile = open(filename, "w")
        for i in range(1, 20):
            line = '"{0}","Treatment Room {1}"\n'.format(randomMAC(), i)
            line += '"{0}","Hand Sanitizer {1}"\n'.format(randomMAC(), i)
            writeFile.write(line)
        rm = rooms.keys()
        for i in rm:
            if(i=="Treatment Room" or i =="Hand Sanitizer"):
                line = '"{0}","{1} 20"\n'.format(rooms[i],i)
            else:
                line = '"{0}","{1}"\n'.format(rooms[i],i)
            writeFile.write(line)
        writeFile.close()
        retroDevices(filename)



def makeDoctorTask(start, user, roomNumber ):
    global prim
    line ='"{0}",'.format(prim)
    prim +=1
    tasks = []
    startTime = start + timedelta(0,randint(-60,60))
    tasks.append((startTime, user, "Office"))

    sessionStart = startTime + timedelta(0,randint(15,45))
    hand_in = sessionStart + timedelta(0,randint(5,15))
    sessionEnd =  hand_in + timedelta(0, floor(abs(np.random.normal(13.53*60,60*3))))
    hand_out = sessionEnd - timedelta(0,randint(10,60))

    tasks.append((sessionStart,user,"Treatment Room {0}".format(roomNumber)))
    if(decision(0.95)):
        tasks.append((hand_in, user,"Hand Sanitizer {0}".format(roomNumber)))
        line += '"1",'
    else:
        line += '"0",'
    if(decision(0.95)):
        tasks.append((hand_out, user,"Hand Sanitizer {0}".format(roomNumber)))
        line += '"1",'
    else:
        line += '"0",'
    tasks.append((sessionEnd,user,"Treatment Room {0}".format(roomNumber)))
    line += '"{0}","{1}","{2}","Treatment Room {3}"\n'.format((sessionEnd-sessionStart),sessionStart,users[user], roomNumber)
    endTime = hand_out + timedelta(0,randint(15,45))
    tasks.append((endTime, user, "Office"))

    return tasks, line

def shiftChange( shift, time):
        transactions = []
        task = PriorityQueue()
        for i in shift:
            start = time + timedelta(0,randint(-300,300))
            task.put((start, i, "Office"))

        while not task.empty():
            transactions.append(task.get())
        return transactions

def retroactiveGenerator(userFile,locationFile):
    # user_device_day = {}
    # user_device_night = {}
    night_shift=[]

    key = userPool.keys()
    scalar = 0.33
    maxNight = floor(scalar*len(key))

    while(len(night_shift)<maxNight):
        randInd = randint(0,len(key)-1)
        if(key[randInd]!=0):
            night_shift.append(key[randInd])
            key[randInd]=0
    day_shift = [x for x in key if x != 0 ]
    maxNightDR = ceil(maxNight*scalar)
    maxDayDr =  ceil(len(day_shift)*scalar)

    dayDrs = []
    nightDrs = []

    while(len(dayDrs)<maxDayDr or len(nightDrs)<maxNightDR):
        if(len(dayDrs)<maxDayDr):
            randDay = randint(0,len(day_shift)-1)
            dayDrs.append(day_shift.pop(randDay))
        if (len(nightDrs)<maxNightDR):
            randNight = randint(0,len(night_shift)-1)
            nightDrs.append(night_shift.pop(randNight))

    dayRNs = day_shift
    nightRNs = night_shift
    day_shift= dayRNs + dayDrs
    night_shift= nightRNs + nightDrs

    # print night_shift,nightDrs
    # print day_shift,dayDrs
# """
    time_format = '%Y-%m-%d %H:%M:%S'

    # start_clock = datetime(2014,1,1,0)
    start_clock = datetime(2014,1,1,0,0)
    end_clock = datetime(2016,12,31,23,59)

    shift = datetime(1,1,1,7,0)
    startBuf1 = (shift - timedelta(0,0,0,0,30)).time()

    shift_start = shift.time()
    shift_end = (shift + timedelta(0,0,0,0,0,12)).time()

    startBuf2 = (shift + timedelta(0,0,0,0,30,11)).time()

    task = PriorityQueue()

    count=1
    write_file = open('access_log_retroactive.csv', 'w')
    # print count
    count+=1
    transactions = shiftChange(night_shift,start_clock)
    # while not task.empty():
    #     transactions.append(task.get())
    file = open("user_stats_retroactive.csv", "w")


    lookup = []
    for i in range(20):
        lookup.append('')
    while (start_clock < end_clock):
        # print start_clock.date()
        if(start_clock.time()==startBuf1):
            pass
        elif(start_clock.time()==shift_start):
            # pass
            taskList = shiftChange(night_shift,start_clock)
            taskList += shiftChange(day_shift,start_clock)
            transactions += sorted(taskList)
        elif(start_clock.time()==startBuf2):
            pass
        elif(start_clock.time()==shift_end):
            # pass
            taskList = shiftChange(day_shift,start_clock)
            taskList += shiftChange(night_shift,start_clock)
            transactions += sorted(taskList)
        elif(start_clock.time()>shift_start and start_clock.time()<shift_end):
            taskList = []
            for i in day_shift:
                while(True):
                    x =randint(0,19)
                    if(lookup[x]==''):
                        lookup[x]=i
                        break
                tmp, line =makeDoctorTask(start_clock,i,x+1)
                file.write(line)

                for k in tmp:
                    taskList.append(k)
            for i in range(20):
                lookup[i] = ''
            for i in taskList:
                task.put(i)
            while not task.empty():
                transactions.append(task.get())
        else:
            taskList = []
            for i in night_shift:
                while(True):
                    x =randint(0,19)
                    if(lookup[x]==''):
                        lookup[x]=i
                        break
                tmp, line =makeDoctorTask(start_clock,i,x+1)
                file.write(line)
                for k in tmp:
                    taskList.append(k)
            for i in range(20):
                lookup[i] = ''
            for i in taskList:
                task.put(i)
            while not task.empty():
                transactions.append(task.get())


        start_clock += timedelta(0,0,0,0,30)
        # print start_clock
    # transactions.sort()
        for i in transactions:
            x = '"{0}","{1}","{2}"\n'.format(roomPool[i[2]],i[1],datetime.strftime(i[0],time_format))
            write_file.write(x)

        transactions = []






    file.close()

    write_file.close
# """
if __name__=="__main__":
# def main():
    retroUsers("user_device_retroactive.csv")
    retroDevices("beacon_device_retroactive.csv")
    # print roomPool.keys()
    retroactiveGenerator("user_device_retroactive.csv", "beacon_device_retroactive.csv")
# main()
