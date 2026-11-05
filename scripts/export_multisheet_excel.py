#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Tornus Procurement Platform — Generador de Reporte Excel Ejecutivo Multihash (.xlsx)
Genera un libro Excel con 3 hojas institucionales:
  1. Metodología y Parámetros (Branded con Logo, Palabras Clave, Entidades y Glosario)
  2. Oportunidades Afines y Cartera (Filtro Tornus)
  3. Mercado General (Todas las licitaciones registradas en la base)
"""

import sys
import os
import json
import sqlite3
from datetime import datetime
import openpyxl
from openpyxl.styles import Font, PatternFill, Alignment, Border, Side
from openpyxl.utils import get_column_letter
from openpyxl.drawing.image import Image

def build_excel_report(db_path, logo_path, output_path, params_json, filters_json_path):
    wb = openpyxl.Workbook()
    wb.remove(wb.active) # Quitar hoja inicial en blanco

    now_str = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    
    try:
        params = json.loads(params_json) if isinstance(params_json, str) else (params_json or {})
    except Exception:
        params = {}

    market = params.get('market', 'CO').upper()
    market_name = "Colombia (SECOP II · Colombia Compra Eficiente)" if market == 'CO' else "Perú (SEACE · Organismo de Contrataciones OECE)"
    currency = "COP ($)" if market == 'CO' else "PEN (S/)"
    source_label = "SECOP II" if market == 'CO' else "OECE Perú"

    # Cargar filtros del archivo JSON si existe
    keywords = []
    exclusions = []
    priority_entities = []
    if os.path.exists(filters_json_path):
        try:
            with open(filters_json_path, 'r', encoding='utf-8') as f:
                f_data = json.load(f)
                keywords = f_data.get('keywords', [])
                exclusions = f_data.get('exclusions', [])
                priority_entities = f_data.get('priority_entities', [])
        except Exception:
            pass

    # -------------------------------------------------------------
    # 1. CONSULTAR DATOS DESDE SQLITE
    # -------------------------------------------------------------
    conn = sqlite3.connect(db_path)
    conn.row_factory = sqlite3.Row
    cur = conn.cursor()

    # Todos los registros vigentes para Sheet 3 (Mercado General)
    cur.execute("""
        SELECT 
            referencia,
            entidad,
            departamento,
            municipio,
            objeto,
            cuantia,
            moneda,
            tipo_proceso,
            categoria,
            estado,
            fecha_publicacion,
            fecha_cierre,
            url_proceso
        FROM licitaciones
        WHERE (fecha_cierre IS NULL OR fecha_cierre >= datetime('now', 'localtime'))
          AND estado NOT IN ('Cancelado', 'Cerrado', 'Desierto', 'Adjudicado')
        ORDER BY cuantia DESC, fecha_cierre DESC
    """)
    raw_rows = cur.fetchall()

    # Registros para Sheet 2 (Filtrados / Cartera / Afinidad)
    # Incluye: procesos en cartera, evaluados por IA, o de entidades prioritarias
    cur.execute("""
        SELECT 
            referencia,
            entidad,
            departamento,
            municipio,
            objeto,
            cuantia,
            moneda,
            tipo_proceso,
            categoria,
            estado,
            fecha_publicacion,
            fecha_cierre,
            score,
            nivel,
            cartera_estado,
            url_proceso
        FROM licitaciones
        WHERE en_cartera = 1 
           OR score >= 50
           OR categoria IN ('estrat', 'pub', 'audio', 'tech', 'evt', 'plan')
        ORDER BY 
            en_cartera DESC,
            COALESCE(score, 0) DESC,
            cuantia DESC
    """)
    filtered_rows = cur.fetchall()

    # Si no hay suficientes por esos criterios, traer los 100 mejores por cuantía
    if len(filtered_rows) == 0:
        cur.execute("""
            SELECT 
                referencia,
                entidad,
                departamento,
                municipio,
                objeto,
                cuantia,
                moneda,
                tipo_proceso,
                categoria,
                estado,
                fecha_publicacion,
                fecha_cierre,
                score,
                nivel,
                cartera_estado,
                url_proceso
            FROM licitaciones
            ORDER BY cuantia DESC
            LIMIT 150
        """)
        filtered_rows = cur.fetchall()

    conn.close()

    total_filtered = len(filtered_rows)
    total_raw = len(raw_rows)

    # -------------------------------------------------------------
    # ESTILOS CORPORATIVOS TORNUS
    # -------------------------------------------------------------
    font_family = "Segoe UI"
    f_title = Font(name=font_family, size=15, bold=True, color="00232C")
    f_subtitle = Font(name=font_family, size=10, color="56686B")
    f_section = Font(name=font_family, size=11, bold=True, color="00232C")
    f_bold = Font(name=font_family, size=9, bold=True, color="00232C")
    f_regular = Font(name=font_family, size=9, color="1E293B")
    f_header = Font(name=font_family, size=10, bold=True, color="FBFB75")
    f_header_teal = Font(name=font_family, size=10, bold=True, color="FFFFFF")

    fill_header_sheet2 = PatternFill(start_color="00232C", end_color="00232C", fill_type="solid") # Navy Tornus
    fill_header_sheet3 = PatternFill(start_color="0B4A57", end_color="0B4A57", fill_type="solid") # Teal Tornus
    fill_meta_head = PatternFill(start_color="F7F2DF", end_color="F7F2DF", fill_type="solid")
    fill_zebra = PatternFill(start_color="FCFAF2", end_color="FCFAF2", fill_type="solid")

    thin_border = Border(
        left=Side(style='thin', color='E3DBC2'),
        right=Side(style='thin', color='E3DBC2'),
        top=Side(style='thin', color='E3DBC2'),
        bottom=Side(style='thin', color='E3DBC2')
    )

    # -------------------------------------------------------------
    # HOJA 1: METODOLOGÍA Y PARÁMETROS INSTITUCIONALES
    # -------------------------------------------------------------
    ws1 = wb.create_sheet(title="Metodología y Parámetros")
    ws1.views.sheetView[0].showGridLines = True

    # 1. Logo
    if os.path.exists(logo_path):
        try:
            img = Image(logo_path)
            img.width = 110
            img.height = 110
            ws1.add_image(img, "B2")
        except Exception:
            pass

    ws1["D2"] = "TORNUS PROCUREMENT PLATFORM"
    ws1["D2"].font = f_title

    ws1["D3"] = f"Reporte Ejecutivo de Inteligencia de Mercado · {market_name}"
    ws1["D3"].font = f_subtitle

    ws1["D4"] = f"Generado: {now_str} | Moneda de referencia: {currency}"
    ws1["D4"].font = Font(name=font_family, size=9, italic=True, color="56686B")

    # Resumen Ejecutivo (KPIs en tabla)
    ws1["B7"] = "RESUMEN EJECUTIVO DEL MERCADO"
    ws1["B7"].font = f_section

    kpi_headers = ["Indicador", "Valor Registrado", "Descripción Operativa"]
    for ci, h in enumerate(kpi_headers, start=2):
        cell = ws1.cell(row=8, column=ci, value=h)
        cell.font = Font(name=font_family, size=9, bold=True, color="00232C")
        cell.fill = fill_meta_head
        cell.border = thin_border

    kpis = [
        ("Territorio Activo", market_name, "Jurisdicción y marco de contratación estatal"),
        ("Procesos Totales en Base", f"{total_raw:,}", "Volumen total de procesos sincronizados"),
        ("Oportunidades Afines Tornus", f"{total_filtered:,}", "Procesos con alta afinidad o seleccionados en Cartera"),
        ("Entidades Prioritarias Activas", f"{len(priority_entities)} entidades", "Instituciones con seguimiento estratégico continuo"),
        ("Palabras Clave de Detección", f"{len(keywords)} términos", "Algoritmo de búsqueda temática en pliegos")
    ]

    for ri, (ind, val, desc) in enumerate(kpis, start=9):
        c1 = ws1.cell(row=ri, column=2, value=ind)
        c2 = ws1.cell(row=ri, column=3, value=val)
        c3 = ws1.cell(row=ri, column=4, value=desc)
        for c in (c1, c2, c3):
            c.font = f_regular
            c.border = thin_border
        c2.font = f_bold

    # Entidades Prioritarias Configuradas
    row_ent = 16
    ws1.cell(row=row_ent, column=2, value="FOCO ESTRATÉGICO · ENTIDADES PRIORITARIAS").font = f_section
    ws1.cell(row=row_ent+1, column=2, value="N°").font = f_bold
    ws1.cell(row=row_ent+1, column=3, value="Entidad / Organismo Estatal").font = f_bold
    ws1.cell(row=row_ent+1, column=4, value="Nivel de Prioridad").font = f_bold
    for col in (2, 3, 4):
        ws1.cell(row=row_ent+1, column=col).fill = fill_meta_head
        ws1.cell(row=row_ent+1, column=col).border = thin_border

    for i, ent in enumerate(priority_entities[:20], start=1):
        r = row_ent + 1 + i
        c_num = ws1.cell(row=r, column=2, value=i)
        c_name = ws1.cell(row=r, column=3, value=ent)
        c_prio = ws1.cell(row=r, column=4, value="Alta · Foco Tornus")
        for c in (c_num, c_name, c_prio):
            c.font = f_regular
            c.border = thin_border

    # Palabras Clave y Criterios
    row_kw = row_ent + len(priority_entities[:20]) + 3
    ws1.cell(row=row_kw, column=2, value="PALABRAS CLAVE DE MONITOREO").font = f_section
    ws1.cell(row=row_kw+1, column=2, value="Términos del Radar: " + ", ".join(keywords[:18])).font = f_regular

    # Ancho de columnas en Hoja 1
    ws1.column_dimensions["A"].width = 4
    ws1.column_dimensions["B"].width = 28
    ws1.column_dimensions["C"].width = 42
    ws1.column_dimensions["D"].width = 48

    # -------------------------------------------------------------
    # HOJA 2: OPORTUNIDADES AFINES Y CARTERA (TORNUS)
    # -------------------------------------------------------------
    ws2 = wb.create_sheet(title="Oportunidades Afines Tornus")
    ws2.views.sheetView[0].showGridLines = True

    headers_ws2 = [
        "Referencia",
        "Entidad Convocante",
        "Región / Dpto",
        "Municipio",
        "Objeto del Proceso",
        f"Presupuesto Base ({currency})",
        "Categoría",
        "Tipo de Proceso",
        "Fecha Cierre",
        "Afinidad IA",
        "Cartera",
        f"Enlace Oficial ({source_label})"
    ]

    for ci, h in enumerate(headers_ws2, start=1):
        cell = ws2.cell(row=1, column=ci, value=h)
        cell.font = f_header
        cell.fill = fill_header_sheet2
        cell.alignment = Alignment(horizontal="center", vertical="center", wrap_text=True)
        cell.border = thin_border
    ws2.row_dimensions[1].height = 28

    for ri, row in enumerate(filtered_rows, start=2):
        ws2.row_dimensions[ri].height = 20
        is_even = (ri % 2 == 0)

        # Mapeo de columnas
        vals = [
            row['referencia'],
            row['entidad'],
            row['departamento'],
            row['municipio'],
            row['objeto'],
            row['cuantia'],
            row['categoria'],
            row['tipo_proceso'],
            row['fecha_cierre'],
            f"{row['score']}% · {row['nivel']}" if row['score'] is not None else "Sin evaluar",
            row['cartera_estado'] or ("Guardado" if row.get('en_cartera') else "En Radar"),
            row['url_proceso']
        ]

        for ci, val in enumerate(vals, start=1):
            cell = ws2.cell(row=ri, column=ci)
            cell.font = f_regular
            cell.border = thin_border
            if is_even:
                cell.fill = fill_zebra

            if ci == 6: # Presupuesto
                try:
                    cell.value = float(val) if val else 0
                    cell.number_format = '$#,##0' if market == 'CO' else 'S/#,##0.00'
                    cell.alignment = Alignment(horizontal="right", vertical="center")
                except:
                    cell.value = val
            elif ci == 12 and str(val).startswith('http'):
                cell.value = f"Ver en {source_label}"
                cell.hyperlink = val
                cell.font = Font(name=font_family, size=9, color="0B4A57", underline="single", bold=True)
                cell.alignment = Alignment(horizontal="center", vertical="center")
            elif ci in (1, 9, 10, 11):
                cell.value = val
                cell.alignment = Alignment(horizontal="center", vertical="center")
            else:
                cell.value = val
                cell.alignment = Alignment(vertical="center")

    # Auto-ancho de columnas
    for col in ws2.columns:
        col_letter = get_column_letter(col[0].column)
        max_l = max(len(str(c.value or '')) for c in col[:50])
        ws2.column_dimensions[col_letter].width = min(max(max_l + 3, 12), 50)
    ws2.column_dimensions["E"].width = 50 # Objeto amplio

    ws2.freeze_panes = "A2"
    ws2.auto_filter.ref = ws2.dimensions

    # -------------------------------------------------------------
    # HOJA 3: TODAS SIN FILTRO (MERCADO GENERAL)
    # -------------------------------------------------------------
    ws3 = wb.create_sheet(title=f"Mercado General {market}")
    ws3.views.sheetView[0].showGridLines = True

    headers_ws3 = [
        "Referencia",
        "Entidad Convocante",
        "Región / Dpto",
        "Municipio",
        "Objeto de Contratación",
        f"Presupuesto Base ({currency})",
        "Modalidad de Contratación",
        "Estado del Proceso",
        "Fecha Publicación",
        "Fecha Cierre",
        f"Enlace Oficial ({source_label})"
    ]

    for ci, h in enumerate(headers_ws3, start=1):
        cell = ws3.cell(row=1, column=ci, value=h)
        cell.font = f_header_teal
        cell.fill = fill_header_sheet3
        cell.alignment = Alignment(horizontal="center", vertical="center", wrap_text=True)
        cell.border = thin_border
    ws3.row_dimensions[1].height = 28

    for ri, row in enumerate(raw_rows, start=2):
        ws3.row_dimensions[ri].height = 19
        is_even = (ri % 2 == 0)

        vals = [
            row['referencia'],
            row['entidad'],
            row['departamento'],
            row['municipio'],
            row['objeto'],
            row['cuantia'],
            row['tipo_proceso'],
            row['estado'],
            row['fecha_publicacion'],
            row['fecha_cierre'],
            row['url_proceso']
        ]

        for ci, val in enumerate(vals, start=1):
            cell = ws3.cell(row=ri, column=ci)
            cell.font = f_regular
            cell.border = thin_border
            if is_even:
                cell.fill = fill_zebra

            if ci == 6:
                try:
                    cell.value = float(val) if val else 0
                    cell.number_format = '$#,##0' if market == 'CO' else 'S/#,##0.00'
                    cell.alignment = Alignment(horizontal="right", vertical="center")
                except:
                    cell.value = val
            elif ci == 11 and str(val).startswith('http'):
                cell.value = f"Ver en {source_label}"
                cell.hyperlink = val
                cell.font = Font(name=font_family, size=9, color="0B4A57", underline="single")
                cell.alignment = Alignment(horizontal="center", vertical="center")
            elif ci in (1, 8, 9, 10):
                cell.value = val
                cell.alignment = Alignment(horizontal="center", vertical="center")
            else:
                cell.value = val
                cell.alignment = Alignment(vertical="center")

    for col in ws3.columns:
        col_letter = get_column_letter(col[0].column)
        max_l = max(len(str(c.value or '')) for c in col[:50])
        ws3.column_dimensions[col_letter].width = min(max(max_l + 3, 12), 45)
    ws3.column_dimensions["E"].width = 50

    ws3.freeze_panes = "A2"
    ws3.auto_filter.ref = ws3.dimensions

    # -------------------------------------------------------------
    # GUARDAR LIBRO COMPLETO
    # -------------------------------------------------------------
    wb.save(output_path)
    print(f"OK: {output_path} ({total_filtered} afines, {total_raw} general)")

if __name__ == "__main__":
    db = sys.argv[1] if len(sys.argv) > 1 else "database/licitaciones_co.sqlite"
    logo = sys.argv[2] if len(sys.argv) > 2 else "public/assets/logo-tornus.png"
    out = sys.argv[3] if len(sys.argv) > 3 else "Reporte_Tornus.xlsx"
    params_str = sys.argv[4] if len(sys.argv) > 4 else "{}"
    filters_file = sys.argv[5] if len(sys.argv) > 5 else "storage/app/radar_filters_co.json"

    build_excel_report(db, logo, out, params_str, filters_file)

