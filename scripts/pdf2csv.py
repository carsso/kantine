import tabula
import sys
tabula.convert_into(sys.argv[1], sys.argv[2], output_format="csv", pages=[1], area=[100,0,540,790])