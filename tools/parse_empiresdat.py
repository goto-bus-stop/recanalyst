"""
Parses string and graphic indices from empires.dat using openage's convert
script.
"""

from openage.openage.convert.gamedata.empiresdat import EmpiresDat
from zlib import decompress
import json
from sys import argv

datfile_path = argv[1]

f = open(datfile_path, 'rb')
data = decompress(f.read(), -15)
empires = EmpiresDat()
empires.read(data, 0)

units = {}
for civ in empires.civs:
    for unitType, unitsOfType in civ.units.items():
        for unit in unitsOfType:
            units[unit.id0] = {
                'name': unit.language_dll_name,
                'graphic': unit.icon_id,
            }

out = {
    'civilizations': [
        civ.name for civ in empires.civs
    ],
    'units': units,
    'researches': {
        research.tech_effect_id: {
            'name': research.language_dll_name,
            'graphic': research.icon_id
        } for research in empires.researches
    }
}

print(json.dumps(out))
