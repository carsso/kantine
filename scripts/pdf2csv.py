import tabula
import sys
from pprint import pprint
if len(sys.argv) != 3:
    print('Usage: python3 pdf2csv.py <input.pdf> <output.csv>')
    exit(1)
for area in [[15,0,85,100], None]:
    print('Working on area: '+str(area))
    dfs = tabula.read_pdf(sys.argv[1], pages=[1], area=area, relative_area=True)
    df = dfs[0]
    pprint(df)
    print("Columns found: "+str(df.columns.values.tolist()))
    if len(df.columns) == 5:
        if type(df.values.tolist()[0][0]) == str and 'lundi' in df.values.tolist()[0][0].lower():
            new_header = df.iloc[0] #grab the first row for the header
            df = df[1:] #take the data less the header row
            df.columns = new_header #set the header row as the df header
        print("Columns changed: "+str(df.columns.values.tolist()))

        if len(df.columns) == 5:
            if type(df.columns[0]) == str and type(df.columns[1]) == str and type(df.columns[2]) == str and type(df.columns[3]) == str and type(df.columns[4]) == str:
                if 'lundi' in df.columns[0].lower() and 'mardi' in df.columns[1].lower() and 'mercredi' in df.columns[2].lower() and 'jeudi' in df.columns[3].lower() and 'vendredi' in df.columns[4].lower():
                    print("Found menu CSV")
                    df.to_csv(sys.argv[2], index=False)
                    pprint(df)
                    exit(0)
    print('No match found for area: '+str(area))
print('No match found for any area')
exit(1)
