#!/usr/bin/python

import os, sys
import csv
from os.path import basename

folder = '../data'
dst_folder = '../src'
dirs = os.listdir( folder )
for filename in dirs:
  with open(folder+'/'+filename, 'r') as csvfile:
      dst_capacity_path = '../src'+'/'+os.path.splitext(basename(filename))[0]+'_capacity.csv'
      dst_feeder_path = '../src'+'/'+os.path.splitext(basename(filename))[0]+'_tr_feeder.csv'
      print dst_feeder_path
      capacity = open(dst_capacity_path,'w')
      feeder = open(dst_feeder_path,'w')
      reader = csv.reader(csvfile, delimiter=',')
      for row in reader:
        lat = row[2]
        lon = row[3]
        mag = row[4] + "0"

        key = lat+lon
        line = key+","+mag+"\n"
        capacity.write(line)
        feeder_line = key+","+lat+","+lon+","+key+"\n"
        feeder.write(feeder_line)

        # print line
        # print feeder_line

      capacity.close() # you can omit in most cases as the destructor will call it
      feeder.close()
