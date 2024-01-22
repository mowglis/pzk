#!/usr/bin/env python3

import urllib.request
import os
import sys
import shutil
import xml.etree.ElementTree as ET
from subprocess import call
import pymysql
import datetime

home_dir="/var/www/pzk/QR"
QR_dir = "/".join([home_dir, "scan_files_dev"])
file_type = "jpeg"

f_ascii = "/".join([home_dir, "ascii.table"])

def make_ascii_dict():
    """ make dict witjh ascii code chars"""
    ascii = {}
    f = open(f_ascii)
    for line in f:
        code,char = line.strip().split(',')
        ascii[code] = char            
    return ascii

def decode(s):
    """ decode string """
    while s.find("#") != -1:
        char0 = s[s.find("#")+1:s.find("#")+4]
        char = str(int(char0)-500)
        s = s.replace("#"+char0, asc[char])
    return s

def read_xml(url):
    """ read xml from url"""
    with urllib.request.urlopen(url) as response:
        xml = response.read()
    return xml.decode('utf-8')

def fetch_tag(tag, qr):
    tag_ = "<"+tag+">"
    _tag = "</"+tag+">"
    t1 = qr[qr.find(tag_)+len(tag_):qr.find(_tag)]
    t2 = qr[0:qr.find(tag_)]+qr[qr.find(_tag)+len(_tag):]
    return t1, t2

def zak_dict(t):
    d = {}
    for it in t.split("<"):
        if it.find(">") < 0:
            continue
        k, v = it.split(">")
        d[k]=decode(v).strip()
    return d        

def zz_dict(t):
    d = {}
    fields = ['prijmeni','jmeno','','titul','']
    z = t[0:t.find("<")]
    zz = z.split(",")
    for i in range(len(zz)):
        d[fields[i]] = decode(zz[i])
    for it in t.split("<"):
        if it.find(">") < 0:
            continue
        k, v = it.split(">")
        d[k.lower()]=decode(v)
    return d        

def print_dict(d,label=""):
    print(" === {} ===".format(label))
    for k in sorted(d.keys()):
        print("{}: {}".format(k,d[k]))

def parse_xml(url):
    """ parse xml from url """
    d = {}
    root = ET.fromstring(read_xml(url))
    elements = [('MistoNar','misto_nar'),('OkresNar','okres_nar'),('StatniObc','statni_obc'),('Skola1','skola_1'),('Skola2','skola_2')]
    for element, key in elements:
        try:
            d[key] = root.find(element).text
        except AttributeError:
            continue
    try:        
        d['pr_1'], d['pr_2'], d['pr_3'] = root.find('Prospech').find('Prumer').text.split(',')
    except:
        pass
    return d

def merge_dicts(x, y):
    """ merge two dicts """
    z = x.copy() 
    z.update(y) 
    return z

def db_connect():
    """ connect db pzk """
    return pymysql.connect(host='localhost', user='insdele', password='_wruser_pzk_123', db='pzk', charset='utf8mb4')

def izo_exist(pzk, izo):
    """ existuje izo v tabulce zs """
    sql = "select * from zs where izo=%s"
    pzk.execute(sql, (izo,))
    result = pzk.fetchone()
    if result == None:
        fields = ('nazev', 'izo')
        vals  = ('*** doplnit ZŠ ***', izo)
        add_rec = "INSERT INTO zs ("+",".join(fields)+") VALUES ("+",".join(['%s' for i in range(len(vals))])+")"
        try:
            pzk.execute(add_rec, vals)
        except pymysql.Error as e:
            print("MySQL error: {}".format(e.args[0], e.args[1]))

def write_rec(uchazec, pzk_c, scan_file):
    """ write rec to table prihlaska/pzk """
    #print(uchazec)
    rec = {}
    dict2rec = [('prijmeni','PR'),('jmeno','JM'),('pohlavi','PH'),('rc','RC'),('e_mail0','EM'),('misto_nar','misto_nar'),('cizinec','statni_obc'),('izo_zs','IZO'),('p1','pr_1'),('p2','pr_3'),('poradi_zajmu','skola_1'),('zast_prijmeni','prijmeni'),('zast_jmeno','jmeno'),('zast_pohlavi','ph'),('e_mail1','em')]
    fields = ()
    vals = ()
    pohlavi = str.maketrans("MZ","12")
    
    for field, key in dict2rec:
        try:
            if key == 'PH' or key == 'ph':
                uchazec[key] = uchazec[key].translate(pohlavi)

            if key == 'statni_obc':
                uchazec[key] = "0" if uchazec[key].split(',')[0] == "203" else "1"
            
            if key == 'skola_1':
                uchazec[key] = "2" if uchazec[key].split(',')[0] != "062690043" else "1"

            if key == 'prijmeni':
                if 'titul' in uchazec:
                    uchazec[key] = " ".join([uchazec['titul'], uchazec[key]])
        
            if key == 'IZO':
                izo_exist(pzk_c, uchazec['IZO'])
                
            vals += (uchazec[key],) 
            fields += (field,)
        except:
            #print("Field error! - {}:{}".format(key, field))
            pass
    
    now = datetime.datetime.now()
    fields += ('scan_date', 'image_file',)
    vals += (now.strftime("%Y-%m-%d %H:%M:%S"), os.path.basename(scan_file),)
    add_rec = "INSERT INTO prihlaska ("+",".join(fields)+") VALUES ("+",".join(['%s' for i in range(len(vals))])+")"
    try:
        pzk_c.execute(add_rec, vals)
        mysql_error = 0
    except pymysql.Error as e:
        mysql_error = " : ".join((str(e.args[0]), str(e.args[1])))
    return mysql_error

def get_num_file(f):
    """ num scan file """
    ff, ext = f.split(".")
    return int(ff.split("_")[len(ff.split("_"))-1])

if __name__ == "__main__":
    asc = make_ascii_dict()
    today = datetime.datetime.now().strftime("%Y-%m-%d") if len(sys.argv)<2 else sys.argv[1]
    print("Files with scan date: {}".format(today))
    QR_files = [os.path.join(QR_dir,f) for f in os.listdir(QR_dir) if os.path.isfile(os.path.join(QR_dir, f)) and f.find(file_type)>-1 and f.split("_")[1]==today]
    pzk = db_connect()
    pzk_c = pzk.cursor()
    l = []
 
    for f in sorted(QR_files, key=get_num_file):
        cmd = "zbarimg -q {}".format(f)
        print("File with QR code: {}".format(os.path.basename(f)), end=" ")
        zbar = os.popen(cmd)
        qr = ""
        for qr in zbar:
            #print("...decoding")
            url, qr = fetch_tag("URL",qr)
            _zz, _zak = fetch_tag("ZZ",qr)
            zz = zz_dict(_zz)
            #print_dict(zz,"zz")
            zak = merge_dicts(zak_dict(_zak), parse_xml(url))
            #print_dict(zak,"zak")
            uchazec = merge_dicts(zak, zz)
            #print_dict(uchazec,"uchazeč")
            #print(uchazec)
            error = write_rec(uchazec, pzk_c, f)
            print(" -- QR decoded ({})".format(error))
        
        if not qr:
            print(" -- QR not found!")
    
    pzk_c.close()
    pzk.commit()
    pzk.close() 
            
