import tabula
import camelot
import sys
from pprint import pprint
if len(sys.argv) != 3:
    print('Usage: python3 pdf2csv.py <input.pdf> <output.csv>')
    exit(1)

def extract_pdf(type):
    if type == 'tabula':
        extract_pdf_tabula()
    elif type == 'camelot':
        extract_pdf_camelot()
    else:
        print('Unknown type: '+str(type))
        exit(1)

def update_df(df):
    if len(df.columns) == 5:
        lines = df.values.tolist()
        end_idx = 0
        print("Columns are: "+str(df.columns.values.tolist()))
        for idx, line in enumerate(lines):
            if type(line[0]) == str and 'lundi' in line[0].lower():
                new_header = df.iloc[idx] # grab the first row for the header
                df = df[idx+1:] # take the data less the header row
                df.columns = new_header # set the header row as the df header
                print("Columns changed: "+str(df.columns.values.tolist()))
                break
        lines = df.values.tolist()
        for idx, line in enumerate(lines):
            for col in line:
                if type(col) == str and "sweet'bar" in col.lower():
                    if end_idx < idx:
                        end_idx = idx
        if end_idx > 0:
            df = df[:end_idx+1] # take the data less the footer row
            print("DF footer removed")
    return df

def check_and_export(df):
    print("Will update DF")
    pprint(df)
    df = update_df(df)
    print("DF updated")
    pprint(df)
    if len(df.columns) == 5:
        lines = df.values.tolist()
        end_found = False
        for col in lines[-1]:
            if type(col) == str and "sweet'bar" in col.lower():
                end_found = True
        if not end_found:
            print("No sweet'bar footer found")
        else:
            if type(df.columns[0]) == str and type(df.columns[1]) == str and type(df.columns[2]) == str and type(df.columns[3]) == str and type(df.columns[4]) == str:
                if 'lundi' in df.columns[0].lower() and 'mardi' in df.columns[1].lower() and 'mercredi' in df.columns[2].lower() and 'jeudi' in df.columns[3].lower() and 'vendredi' in df.columns[4].lower():
                    print("Found menu CSV")
                    df.to_csv(sys.argv[2], index=False)
                    exit(0)
    print('Export invalid, skipping it')

def extract_pdf_camelot():
    tables = camelot.read_pdf(sys.argv[1], pages='1', flavor='stream')
    if len(tables) == 0:
        print('No DF found')
        return
    table = tables[0]
    check_and_export(table.df)

def extract_pdf_tabula():
    for area in [[15,0,85,100], None]:
        print('Working on area: '+str(area))
        dfs = tabula.read_pdf(sys.argv[1], pages=[1], area=area, relative_area=True)
        if len(dfs) == 0:
            print('No DF found')
            continue
        check_and_export(dfs[0])

print('Trying tabula')
extract_pdf_tabula()
print('Trying camelot')
extract_pdf_camelot()

print('Failed to extract PDF')
exit(1)