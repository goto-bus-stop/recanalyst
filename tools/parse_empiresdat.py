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

players = [
    player.minimap_color for player in empires.player_colors
]

terrains = [
    {
        'name': terrain.name0,
        'graphic': terrain.slp_id,
        'minimap': [
            terrain.map_color_hi,
            terrain.map_color_med,
            terrain.map_color_low,
            terrain.map_color_cliff_lt,
            terrain.map_color_cliff_rt,
        ],
    } for terrain in empires.terrains
]

units = {}
for civ in empires.civs:
    for unitType, unitsOfType in civ.units.items():
        for unit in unitsOfType:
            units[unit.id0] = {
                'name': unit.language_dll_name,
                'graphic': unit.icon_id,
            }

researches = {}
index = 0
for research in empires.researches:
    researches[index] = {
        'name': research.language_dll_name,
        'graphic': research.icon_id
    }
    index += 1

out = {
    'player_colors': players,
    'terrain_colors': terrains,
    'civilizations': [
        civ.name for civ in empires.civs
    ],
    'units': units,
    'researches': researches,
}

print(json.dumps(out))
