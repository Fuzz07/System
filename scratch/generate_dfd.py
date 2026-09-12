"""
Complete Generator for SSC_DFD.drawio (Pure Black & White, Clean Box Design)
Generates:
  Page 1: Level 0 - Context Diagram
  Page 2: Level 1 - Core Subsystems DFD
  Page 3: Level 2 - 1.0 Auth, Whitelist & User Management
  Page 4: Level 2 - 2.0 Enrollment Fee Payment & Verification
  Page 5: Level 2 - 3.0 Budget Allocation & Fiscal Oversight
  Page 6: Level 2 - 4.0 Proposals, Releases, Expenses & Liquidation
  Page 7: Level 2 - 5.0 Candidacy Filing & Secret Ballot Election
  Page 8: Level 2 - 6.0 Announcements, Community Feedback & Audit Logs

Features:
- Pure Black & White (white fill #FFFFFF, black stroke #000000, black text #000000)
- Clean, closed, fully wrapped boxes for all data stores, processes, and entities
- Zero overlapping or tangled lines (dedicated anchor ports + multi-channel bus waypoints)
"""

import xml.etree.ElementTree as ET
import os

# ─────────────────────────────────────────────────────────────────────────────
# STYLING CONSTANTS (PURE BLACK & WHITE)
# ─────────────────────────────────────────────────────────────────────────────

TITLE_STYLE = (
    'text;html=1;whiteSpace=wrap;align=center;verticalAlign=middle;'
    'fontFamily=Helvetica;fontStyle=1;fontSize=15;fillColor=#FFFFFF;'
    'fontColor=#000000;rounded=0;strokeColor=#000000;strokeWidth=1.5;'
)

ENTITY_STYLE = (
    'rounded=0;whiteSpace=wrap;html=1;fillColor=#FFFFFF;strokeColor=#000000;'
    'strokeWidth=2;fontColor=#000000;fontFamily=Helvetica;align=center;'
    'verticalAlign=middle;fontStyle=1;fontSize=12;shadow=0;'
)

PROCESS_STYLE = (
    'rounded=1;arcSize=14;whiteSpace=wrap;html=1;fillColor=#FFFFFF;strokeColor=#000000;'
    'strokeWidth=2;fontColor=#000000;fontFamily=Helvetica;align=center;'
    'verticalAlign=middle;fontStyle=0;shadow=0;'
)

# Clean, closed, fully bordered data store box with text wrapping
STORE_STYLE = (
    'rounded=0;whiteSpace=wrap;html=1;fillColor=#FFFFFF;strokeColor=#000000;'
    'strokeWidth=1.5;fontColor=#000000;fontFamily=Helvetica;align=left;'
    'spacingLeft=10;spacingRight=10;verticalAlign=middle;fontSize=10;shadow=0;'
)

FLOW_STYLE = (
    'edgeStyle=orthogonalEdgeStyle;rounded=1;orthogonalLoop=1;jettySize=auto;html=1;'
    'strokeColor=#000000;strokeWidth=1.5;fontSize=10;fontFamily=Helvetica;fontColor=#000000;'
    'align=center;verticalAlign=bottom;labelBackgroundColor=#FFFFFF;spacing=2;'
)

# ─────────────────────────────────────────────────────────────────────────────
# HELPER FUNCTIONS
# ─────────────────────────────────────────────────────────────────────────────

def add_cell(root, **kwargs):
    return ET.SubElement(root, 'mxCell', {k: str(v) for k, v in kwargs.items()})

def set_geo(cell, x, y, w, h, rel=False):
    attrs = {'x': str(x), 'y': str(y), 'width': str(w), 'height': str(h), 'as': 'geometry'}
    if rel:
        attrs['relative'] = '1'
    return ET.SubElement(cell, 'mxGeometry', attrs)

def create_title(root, tid, title, subtitle, x=400, y=30, w=1200, h=65):
    c = add_cell(root, id=tid, parent='1', vertex='1', style=TITLE_STYLE,
                 value=f'<b>{title}</b><br><span style="font-size:12px;font-weight:normal;color:#333333;">{subtitle}</span>')
    set_geo(c, x, y, w, h)
    return c

def create_entity(root, eid, title, subtitle="", x=100, y=100, w=180, h=80):
    val = f'<b>{title}</b>'
    if subtitle:
        val += f'<br><font style="font-size:10px;font-weight:normal;color:#333333;">{subtitle}</font>'
    c = add_cell(root, id=eid, parent='1', vertex='1', style=ENTITY_STYLE, value=val)
    set_geo(c, x, y, w, h)
    return c

def create_process(root, pid, num, name, x=600, y=200, w=240, h=80):
    val = f'<b style="font-size:13px;color:#000000;">{num}</b><br><span style="font-size:11px;color:#000000;">{name}</span>'
    c = add_cell(root, id=pid, parent='1', vertex='1', style=PROCESS_STYLE, value=val)
    set_geo(c, x, y, w, h)
    return c

def create_datastore(root, did, code, name, fields="", x=1100, y=200, w=360, h=70):
    val = f'<b style="font-size:11px;color:#000000;">{code}: {name}</b>'
    if fields:
        val += f'<br><font color="#333333" style="font-size:9px;">{fields}</font>'
    c = add_cell(root, id=did, parent='1', vertex='1', style=STORE_STYLE, value=val)
    set_geo(c, x, y, w, h)
    return c

def create_flow(root, fid, src, tgt, label, sx=None, sy=None, ex=None, ey=None, points=None):
    attrs = {
        'id': fid, 'parent': '1', 'edge': '1',
        'source': src, 'target': tgt,
        'style': FLOW_STYLE, 'value': label
    }
    if sx is not None:
        attrs['exitX'] = str(sx); attrs['exitY'] = str(sy)
        attrs['exitDx'] = '0'; attrs['exitDy'] = '0'
    if ex is not None:
        attrs['entryX'] = str(ex); attrs['entryY'] = str(ey)
        attrs['entryDx'] = '0'; attrs['entryDy'] = '0'
    
    cell = ET.SubElement(root, 'mxCell', attrs)
    geo = ET.SubElement(cell, 'mxGeometry', {'relative': '1', 'as': 'geometry'})
    if points:
        arr = ET.SubElement(geo, 'Array', {'as': 'points'})
        for px, py in points:
            ET.SubElement(arr, 'mxPoint', {'x': str(px), 'y': str(py)})
    return cell

# ─────────────────────────────────────────────────────────────────────────────
# BUILD DIAGRAMS (PURE BLACK & WHITE, CLEAN BOXES)
# ─────────────────────────────────────────────────────────────────────────────

def build_complete_dfd():
    mxfile = ET.Element('mxfile', {'host': 'Electron', 'agent': 'Antigravity IDE', 'version': '21.0.0'})

    # ══════════════════════════════════════════════════════════════════════════
    # PAGE 1: LEVEL 0 - CONTEXT DIAGRAM
    # ══════════════════════════════════════════════════════════════════════════
    d0 = ET.SubElement(mxfile, 'diagram', {'id': 'dfd-level-0', 'name': 'Level 0 - Context Diagram'})
    m0 = ET.SubElement(d0, 'mxGraphModel', {'dx': '1800', 'dy': '1200', 'grid': '0', 'page': '1', 'pageWidth': '1800', 'pageHeight': '1200'})
    r0 = ET.SubElement(m0, 'root')
    add_cell(r0, id='0')
    add_cell(r0, id='1', parent='0')

    create_title(r0, 't0', 'Level 0 – Context Diagram',
                 'SSC Transparency & Budget Management System (High-Level Boundary & Environment)',
                 x=300, y=30, w=1200, h=65)

    # Central System 0.0
    create_process(r0, 'p0', '0.0', 'SSC Transparency &amp;<br>Budget Management System',
                   x=720, y=440, w=320, h=140)

    # External Entities
    create_entity(r0, 'e_adm', 'ADMIN / ADVISER', 'Financial Approver &amp; System Oversight',
                  x=120, y=180, w=240, h=140)
    create_entity(r0, 'e_off', 'SSC OFFICER', 'Proposal Proponent, Disburser &amp; Event Head',
                  x=120, y=700, w=240, h=140)
    create_entity(r0, 'e_dean', 'COLLEGE DEAN', 'Department Candidacy Evaluator',
                  x=1440, y=180, w=240, h=120)
    create_entity(r0, 'e_stud', 'STUDENT BODY', 'Voter, Fee Payer, Feedback Sender &amp; Transparency Viewer',
                  x=1440, y=560, w=240, h=240)

    # Flows: Admin <-> System
    create_flow(r0, 'f0_a1', 'e_adm', 'p0', 'User Roles &amp; System Config',
                sx=1.0, sy=0.20, ex=0.15, ey=0.0, points=[(768, 208)])
    create_flow(r0, 'f0_a2', 'e_adm', 'p0', 'Department Budget Allocations',
                sx=1.0, sy=0.45, ex=0.0, ey=0.20, points=[(540, 243), (540, 468)])
    create_flow(r0, 'f0_a3', 'e_adm', 'p0', 'Proposal &amp; Liquidation Approvals',
                sx=1.0, sy=0.70, ex=0.0, ey=0.35, points=[(600, 278), (600, 489)])
    create_flow(r0, 'f0_a4', 'p0', 'e_adm', 'Consolidated Financial Reports',
                sx=0.0, sy=0.50, ex=1.0, ey=0.88, points=[(480, 510), (480, 303)])
    create_flow(r0, 'f0_a5', 'p0', 'e_adm', 'Activity Audit Logs &amp; Trails',
                sx=0.30, sy=0.0, ex=0.65, ey=1.0, points=[(816, 380), (276, 380)])

    # Flows: Officer <-> System
    create_flow(r0, 'f0_o1', 'e_off', 'p0', 'Project Proposals &amp; Estimates',
                sx=1.0, sy=0.25, ex=0.0, ey=0.65, points=[(600, 735), (600, 531)])
    create_flow(r0, 'f0_o2', 'e_off', 'p0', 'Expense Claims &amp; Receipts',
                sx=1.0, sy=0.50, ex=0.0, ey=0.80, points=[(540, 770), (540, 552)])
    create_flow(r0, 'f0_o3', 'e_off', 'p0', 'Post-Event Liquidation Reports',
                sx=1.0, sy=0.75, ex=0.15, ey=1.0, points=[(768, 805)])
    create_flow(r0, 'f0_o4', 'p0', 'e_off', 'Approved Proposal Status &amp; Releases',
                sx=0.0, sy=0.92, ex=0.75, ey=0.0, points=[(480, 569), (480, 650), (300, 650)])
    create_flow(r0, 'f0_o5', 'p0', 'e_off', 'Department Budget Balance Info',
                sx=0.30, sy=1.0, ex=1.0, ey=0.92, points=[(816, 860), (420, 860), (420, 829)])

    # Flows: Dean <-> System
    create_flow(r0, 'f0_d1', 'e_dean', 'p0', 'Candidate Endorsement &amp; Vetting',
                sx=0.0, sy=0.35, ex=0.85, ey=0.0, points=[(992, 222)])
    create_flow(r0, 'f0_d2', 'p0', 'e_dean', 'Department Candidate Profiles',
                sx=0.70, sy=0.0, ex=0.0, ey=0.75, points=[(944, 270)])

    # Flows: Student <-> System
    create_flow(r0, 'f0_s1', 'e_stud', 'p0', 'Student Registration &amp; Credentials',
                sx=0.0, sy=0.15, ex=1.0, ey=0.20, points=[(1240, 596), (1240, 468)])
    create_flow(r0, 'f0_s2', 'e_stud', 'p0', 'Candidacy Application &amp; Platform',
                sx=0.0, sy=0.30, ex=1.0, ey=0.35, points=[(1180, 632), (1180, 489)])
    create_flow(r0, 'f0_s3', 'e_stud', 'p0', 'Secret Ballot Votes',
                sx=0.0, sy=0.45, ex=1.0, ey=0.50, points=[(1120, 668), (1120, 510)])
    create_flow(r0, 'f0_s4', 'e_stud', 'p0', 'Enrollment Fee Proof (GCash)',
                sx=0.0, sy=0.60, ex=1.0, ey=0.65, points=[(1180, 704), (1180, 531)])
    create_flow(r0, 'f0_s5', 'e_stud', 'p0', 'Feedback, Inquiries &amp; Grievances',
                sx=0.0, sy=0.75, ex=1.0, ey=0.80, points=[(1240, 740), (1240, 552)])
    create_flow(r0, 'f0_s6', 'e_stud', 'p0', 'Comments on Announcements',
                sx=0.0, sy=0.90, ex=0.85, ey=1.0, points=[(992, 776)])

    create_flow(r0, 'f0_s7', 'p0', 'e_stud', 'Public Transparency Dashboard',
                sx=1.0, sy=0.10, ex=0.25, ey=0.0, points=[(1500, 454)])
    create_flow(r0, 'f0_s8', 'p0', 'e_stud', 'Certified Live Election Results',
                sx=1.0, sy=0.25, ex=0.60, ey=0.0, points=[(1300, 475), (1300, 515), (1584, 515)])
    create_flow(r0, 'f0_s9', 'p0', 'e_stud', 'Announcements &amp; Event Feed',
                sx=1.0, sy=0.75, ex=0.40, ey=1.0, points=[(1080, 545), (1080, 850), (1536, 850)])
    create_flow(r0, 'f0_s10', 'p0', 'e_stud', 'Feedback Replies &amp; Payment Clearances',
                sx=0.70, sy=1.0, ex=0.75, ey=1.0, points=[(944, 890), (1620, 890)])


    # ══════════════════════════════════════════════════════════════════════════
    # PAGE 2: LEVEL 1 - CORE SUBSYSTEMS DFD
    # ══════════════════════════════════════════════════════════════════════════
    d1 = ET.SubElement(mxfile, 'diagram', {'id': 'dfd-level-1', 'name': 'Level 1 - Core Subsystems'})
    m1 = ET.SubElement(d1, 'mxGraphModel', {'dx': '2600', 'dy': '3200', 'grid': '0', 'page': '1', 'pageWidth': '2600', 'pageHeight': '3200'})
    r1 = ET.SubElement(m1, 'root')
    add_cell(r1, id='0')
    add_cell(r1, id='1', parent='0')

    create_title(r1, 't1', 'Level 1 – Core Subsystems Data Flow Diagram',
                 'Decomposition of Process 0.0 into 6 Primary Functional Subsystems & Data Stores',
                 x=600, y=30, w=1400, h=65)

    PX = 750
    PW = 320
    PH = 95
    procs_l1 = [
        ('p1_1', '1.0', 'User Management, Authentication<br>&amp; Whitelist Verification', 180),
        ('p1_2', '2.0', 'Enrollment Fee Payment<br>&amp; Proof Verification',             640),
        ('p1_3', '3.0', 'Budget Allocation &amp;<br>Fiscal Setup Oversight',             1100),
        ('p1_4', '4.0', 'Project Proposals, Releases,<br>Expenses &amp; Liquidation',     1580),
        ('p1_5', '5.0', 'Candidacy Filing &amp;<br>Secret Ballot Election',               2120),
        ('p1_6', '6.0', 'Announcements, Community<br>Feedback &amp; Audit Logs',          2640),
    ]
    for pid, num, name, py in procs_l1:
        create_process(r1, pid, num, name, x=PX, y=py, w=PW, h=PH)

    # Left Entities
    create_entity(r1, 'l1_e_stud1', 'STUDENT', 'Account Holder / Payer', x=80, y=260, w=180, h=100)
    create_entity(r1, 'l1_e_adm1',  'ADMIN / ADVISER', 'Governance &amp; Approver', x=80, y=800, w=180, h=110)
    create_entity(r1, 'l1_e_off',   'SSC OFFICER', 'Proponent &amp; Disburser', x=80, y=1400, w=180, h=110)
    create_entity(r1, 'l1_e_dean',  'COLLEGE DEAN', 'Department Evaluator', x=80, y=2000, w=180, h=90)
    create_entity(r1, 'l1_e_stud2', 'STUDENT BODY', 'Voter &amp; Community', x=80, y=2400, w=180, h=110)

    # Right Data Stores: Generous width=360, height=65, clean boxes with wrapping
    DX = 1350
    DW = 360
    stores_l1 = [
        ('ds_d1', 'D1', 'eligible_students', 'student_id, full_name, course, year_level, is_registered', 160),
        ('ds_d2', 'D2', 'users', 'id, student_id, name, email, role, status', 240),
        ('ds_d3', 'D3', 'enrollment_payments', 'id, user_id, amount, payment_method, proof_file, proof_status', 655),
        ('ds_d15', 'D15', 'school_years', 'id, year, semester, is_active, voting_open, candidacy_open', 1060),
        ('ds_d4', 'D4', 'budgets', 'id, title, department, allocated_amount, remaining_balance', 1145),
        ('ds_d5', 'D5', 'proposals', 'id, officer_id, title, requested_budget, approved_budget, status', 1500),
        ('ds_d6', 'D6', 'budget_releases', 'id, budget_id, proposal_id, amount, released_at', 1580),
        ('ds_d7', 'D7', 'expenses', 'id, budget_id, officer_id, title, amount, receipt_path', 1660),
        ('ds_d8', 'D8', 'liquidations', 'id, proposal_id, officer_id, report_document, status', 1740),
        ('ds_d9', 'D9', 'candidacies', 'id, user_id, position, platform, photo, dean_status, status', 2040),
        ('ds_d10', 'D10', 'student_ballots', 'id, user_id, school_year_id, status, submitted_at', 2125),
        ('ds_d11', 'D11', 'votes', 'id, user_id, candidacy_id, position, is_skip, created_at', 2205),
        ('ds_d12', 'D12', 'announcements', 'id, title, content, category, image_file, project_id', 2560),
        ('ds_d12c', 'D12.1', 'announcement_comments', 'id, announcement_id, user_id, comment, created_at', 2640),
        ('ds_d13', 'D13', 'feedback', 'id, student_id, message, reply, status, replied_by', 2720),
        ('ds_d14', 'D14', 'activity_logs', 'id, user_id, action, details, ip_address, created_at', 2800),
    ]
    for did, dnum, dname, dfld, dy in stores_l1:
        create_datastore(r1, did, dnum, dname, dfld, x=DX, y=dy, w=DW, h=65)

    # Flows for Subsystem 1.0
    create_flow(r1, 'f1_s1', 'l1_e_stud1', 'p1_1', 'Registration Form: {student_id, name, email, pass}',
                sx=1.0, sy=0.30, ex=0.0, ey=0.30, points=[(420, 290), (420, 208)])
    create_flow(r1, 'f1_p1_s', 'p1_1', 'l1_e_stud1', 'Auth Token &amp; User Profile',
                sx=0.0, sy=0.70, ex=1.0, ey=0.70, points=[(360, 246), (360, 330)])
    create_flow(r1, 'f1_p1_d1', 'p1_1', 'ds_d1', 'Verify whitelist eligibility',
                sx=1.0, sy=0.25, ex=0.0, ey=0.50)
    create_flow(r1, 'f1_p1_d2', 'p1_1', 'ds_d2', 'INSERT user credentials',
                sx=1.0, sy=0.75, ex=0.0, ey=0.50)
    create_flow(r1, 'f1_d2_p1', 'ds_d2', 'p1_1', 'Authenticate login session',
                sx=0.0, sy=0.85, ex=0.75, ey=1.0, points=[(1260, 295), (1260, 320), (990, 320)])

    # Flows for Subsystem 2.0
    create_flow(r1, 'f1_s2', 'l1_e_stud1', 'p1_2', 'Payment Proof: {amount, method, proof_file}',
                sx=0.75, sy=1.0, ex=0.0, ey=0.30, points=[(215, 668)])
    create_flow(r1, 'f1_p2_s', 'p1_2', 'l1_e_stud1', 'Verification Clearance Status',
                sx=0.0, sy=0.70, ex=0.40, ey=1.0, points=[(320, 706), (320, 420), (152, 420)])
    create_flow(r1, 'f1_a1_p2', 'l1_e_adm1', 'p1_2', 'Review Payment: {payment_id, action: approve/reject}',
                sx=1.0, sy=0.20, ex=0.20, ey=1.0, points=[(460, 822), (460, 760), (814, 760)])
    create_flow(r1, 'f1_p2_d3', 'p1_2', 'ds_d3', 'INSERT / UPDATE payment proof &amp; status',
                sx=1.0, sy=0.40, ex=0.0, ey=0.35)
    create_flow(r1, 'f1_d3_p2', 'ds_d3', 'p1_2', 'Fetch pending payment records',
                sx=0.0, sy=0.75, ex=1.0, ey=0.75)

    # Flows for Subsystem 3.0
    create_flow(r1, 'f1_a1_p3', 'l1_e_adm1', 'p1_3', 'Allocate Budget: {department, allocated_amount}',
                sx=1.0, sy=0.50, ex=0.0, ey=0.35, points=[(520, 855), (520, 1133)])
    create_flow(r1, 'f1_p3_sy', 'p1_3', 'ds_d15', 'Fetch active fiscal period',
                sx=1.0, sy=0.25, ex=0.0, ey=0.50)
    create_flow(r1, 'f1_p3_d4', 'p1_3', 'ds_d4', 'INSERT / UPDATE budget allocation lines',
                sx=1.0, sy=0.65, ex=0.0, ey=0.50)
    create_flow(r1, 'f1_p3_off', 'p1_3', 'l1_e_off', 'Department Budget Summary &amp; Balance',
                sx=0.0, sy=0.75, ex=1.0, ey=0.25, points=[(440, 1171), (440, 1427)])

    # Flows for Subsystem 4.0
    create_flow(r1, 'f1_off_p4', 'l1_e_off', 'p1_4', 'Submit Proposal &amp; Liquidation Reports',
                sx=1.0, sy=0.50, ex=0.0, ey=0.25, points=[(540, 1455), (540, 1607)])
    create_flow(r1, 'f1_adm_p4', 'l1_e_adm1', 'p1_4', 'Audit &amp; Approve: {proposal_id, approved_budget}',
                sx=1.0, sy=0.85, ex=0.0, ey=0.50, points=[(380, 893), (380, 1635)])
    create_flow(r1, 'f1_p4_off', 'p1_4', 'l1_e_off', 'Proposal Approval &amp; Release Confirmation',
                sx=0.0, sy=0.80, ex=1.0, ey=0.80, points=[(480, 1668), (480, 1488)])
    create_flow(r1, 'f1_p4_d5', 'p1_4', 'ds_d5', 'INSERT / UPDATE proposals',
                sx=1.0, sy=0.20, ex=0.0, ey=0.50)
    create_flow(r1, 'f1_p4_d6', 'p1_4', 'ds_d6', 'INSERT budget_releases record',
                sx=1.0, sy=0.45, ex=0.0, ey=0.50)
    create_flow(r1, 'f1_p4_d7', 'p1_4', 'ds_d7', 'INSERT expense receipts',
                sx=1.0, sy=0.70, ex=0.0, ey=0.50)
    create_flow(r1, 'f1_p4_d8', 'p1_4', 'ds_d8', 'INSERT liquidation documents',
                sx=1.0, sy=0.90, ex=0.0, ey=0.50)

    # Flows for Subsystem 5.0
    create_flow(r1, 'f1_dean_p5', 'l1_e_dean', 'p1_5', 'Dean Endorsement: {candidacy_id, decision}',
                sx=1.0, sy=0.50, ex=0.0, ey=0.25, points=[(480, 2045), (480, 2147)])
    create_flow(r1, 'f1_s2_p5', 'l1_e_stud2', 'p1_5', 'Candidacy Form &amp; Secret Ballot',
                sx=1.0, sy=0.30, ex=0.0, ey=0.60, points=[(540, 2433), (540, 2186)])
    create_flow(r1, 'f1_p5_s2', 'p1_5', 'l1_e_stud2', 'Certified Election Winners &amp; Tally',
                sx=0.0, sy=0.85, ex=1.0, ey=0.75, points=[(420, 2213), (420, 2482)])
    create_flow(r1, 'f1_p5_d9', 'p1_5', 'ds_d9', 'Manage candidacy profiles',
                sx=1.0, sy=0.25, ex=0.0, ey=0.50)
    create_flow(r1, 'f1_p5_d10', 'p1_5', 'ds_d10', 'Record student voter ballot status',
                sx=1.0, sy=0.55, ex=0.0, ey=0.50)
    create_flow(r1, 'f1_p5_d11', 'p1_5', 'ds_d11', 'INSERT encrypted secret votes',
                sx=1.0, sy=0.85, ex=0.0, ey=0.50)

    # Flows for Subsystem 6.0
    create_flow(r1, 'f1_s2_p6', 'l1_e_stud2', 'p1_6', 'Submit Feedback &amp; Lost Item Comments',
                sx=0.70, sy=1.0, ex=0.0, ey=0.35, points=[(206, 2673)])
    create_flow(r1, 'f1_p6_s2', 'p1_6', 'l1_e_stud2', 'Live Announcements &amp; Feedback Responses',
                sx=0.0, sy=0.70, ex=0.30, ey=1.0, points=[(340, 2707), (340, 2550), (134, 2550)])
    create_flow(r1, 'f1_p6_d12', 'p1_6', 'ds_d12', 'Publish / retrieve announcements',
                sx=1.0, sy=0.20, ex=0.0, ey=0.50)
    create_flow(r1, 'f1_p6_d12c', 'p1_6', 'ds_d12c', 'INSERT comments on announcement items',
                sx=1.0, sy=0.45, ex=0.0, ey=0.50)
    create_flow(r1, 'f1_p6_d13', 'p1_6', 'ds_d13', 'INSERT / UPDATE feedback messages',
                sx=1.0, sy=0.70, ex=0.0, ey=0.50)
    create_flow(r1, 'f1_p6_d14', 'p1_6', 'ds_d14', 'Append system audit &amp; security logs',
                sx=1.0, sy=0.90, ex=0.0, ey=0.50)


    # ══════════════════════════════════════════════════════════════════════════
    # PAGE 3: LEVEL 2 - 1.0 AUTH, WHITELIST & USER MANAGEMENT
    # ══════════════════════════════════════════════════════════════════════════
    d2_1 = ET.SubElement(mxfile, 'diagram', {'id': 'dfd-level-2-auth', 'name': 'Level 2 - 1.0 Auth & Whitelist'})
    m2_1 = ET.SubElement(d2_1, 'mxGraphModel', {'dx': '1800', 'dy': '1200', 'grid': '0', 'page': '1', 'pageWidth': '1800', 'pageHeight': '1200'})
    r2_1 = ET.SubElement(m2_1, 'root')
    add_cell(r2_1, id='0')
    add_cell(r2_1, id='1', parent='0')

    create_title(r2_1, 't2_1', 'Level 2 – 1.0 User Management, Authentication & Whitelist Verification',
                 'Detailed functional decomposition of enrollment roster verification and secure login',
                 x=300, y=30, w=1200, h=65)

    create_entity(r2_1, 'e2_stud', 'STUDENT', 'Registrant / User', x=100, y=260, w=180, h=100)
    create_entity(r2_1, 'e2_adm',  'ADMIN / ADVISER', 'Security Administrator', x=100, y=700, w=180, h=100)

    create_process(r2_1, 'p2_1_1', '1.1', 'Roster Whitelist<br>Eligibility Check', x=460, y=180, w=220, h=80)
    create_process(r2_1, 'p2_1_2', '1.2', 'Student Account<br>Registration &amp; Hashing', x=460, y=360, w=220, h=80)
    create_process(r2_1, 'p2_1_3', '1.3', 'Credential Authentication<br>&amp; Session Token Grant', x=460, y=540, w=220, h=80)
    create_process(r2_1, 'p2_1_4', '1.4', 'Role Assignment &amp;<br>Account Status Governance', x=460, y=720, w=220, h=80)

    create_datastore(r2_1, 'ds2_d1', 'D1', 'eligible_students', 'student_id, full_name, course, is_registered', x=950, y=185, w=350, h=65)
    create_datastore(r2_1, 'ds2_d2', 'D2', 'users', 'id, student_id, name, email, password, role, status', x=950, y=450, w=350, h=70)
    create_datastore(r2_1, 'ds2_d14', 'D14', 'activity_logs', 'id, user_id, action, details, ip_address, created_at', x=950, y=725, w=350, h=65)

    create_flow(r2_1, 'f2_1_1', 'e2_stud', 'p2_1_1', 'Input: {student_id, birthdate}', sx=1.0, sy=0.30, ex=0.0, ey=0.50, points=[(340, 290), (340, 220)])
    create_flow(r2_1, 'f2_1_2', 'p2_1_1', 'ds2_d1', 'SELECT * WHERE student_id=input AND is_registered=0', sx=1.0, sy=0.50, ex=0.0, ey=0.50)
    create_flow(r2_1, 'f2_1_3', 'p2_1_1', 'p2_1_2', 'Eligibility Verified: {student_id, verified_name}', sx=0.50, sy=1.0, ex=0.50, ey=0.0)
    create_flow(r2_1, 'f2_1_4', 'e2_stud', 'p2_1_2', 'Submit: {email, password, password_confirmation}', sx=1.0, sy=0.70, ex=0.0, ey=0.50, points=[(380, 330), (380, 400)])
    create_flow(r2_1, 'f2_1_5', 'p2_1_2', 'ds2_d2', 'INSERT INTO users (password=Hash::make(...))', sx=1.0, sy=0.50, ex=0.0, ey=0.35)
    create_flow(r2_1, 'f2_1_6', 'p2_1_2', 'ds2_d1', 'UPDATE eligible_students SET is_registered=1', sx=0.75, sy=0.0, ex=0.50, ey=1.0, points=[(625, 300), (1125, 300)])
    create_flow(r2_1, 'f2_1_7', 'e2_stud', 'p2_1_3', 'Login: {email, password}', sx=0.50, sy=1.0, ex=0.0, ey=0.35, points=[(190, 568)])
    create_flow(r2_1, 'f2_1_8', 'ds2_d2', 'p2_1_3', 'Validate Hash &amp; Role', sx=0.0, sy=0.75, ex=1.0, ey=0.50, points=[(820, 502), (820, 580)])
    create_flow(r2_1, 'f2_1_9', 'p2_1_3', 'e2_stud', 'Grant Session / Sanctum Bearer Token', sx=0.0, sy=0.75, ex=0.25, ey=1.0, points=[(340, 600), (340, 420), (145, 420)])
    create_flow(r2_1, 'f2_1_10', 'e2_adm', 'p2_1_4', 'Assign Role / Approve Status: {user_id, role}', sx=1.0, sy=0.50, ex=0.0, ey=0.50)
    create_flow(r2_1, 'f2_1_11', 'p2_1_4', 'ds2_d2', 'UPDATE users SET role=..., status=...', sx=1.0, sy=0.30, ex=0.25, ey=1.0, points=[(840, 744), (840, 580), (1037, 580)])
    create_flow(r2_1, 'f2_1_12', 'p2_1_4', 'ds2_d14', 'Log Admin Audit Action', sx=1.0, sy=0.70, ex=0.0, ey=0.50)


    # ══════════════════════════════════════════════════════════════════════════
    # PAGE 4: LEVEL 2 - 2.0 ENROLLMENT FEE PAYMENT & VERIFICATION
    # ══════════════════════════════════════════════════════════════════════════
    d2_2 = ET.SubElement(mxfile, 'diagram', {'id': 'dfd-level-2-enrollment', 'name': 'Level 2 - 2.0 Enrollment Payment'})
    m2_2 = ET.SubElement(d2_2, 'mxGraphModel', {'dx': '1800', 'dy': '1200', 'grid': '0', 'page': '1', 'pageWidth': '1800', 'pageHeight': '1200'})
    r2_2 = ET.SubElement(m2_2, 'root')
    add_cell(r2_2, id='0')
    add_cell(r2_2, id='1', parent='0')

    create_title(r2_2, 't2_2', 'Level 2 – 2.0 Enrollment Fee Payment & Verification',
                 'Detailed data flow for payment submission, receipt proof upload & administrative confirmation',
                 x=300, y=30, w=1200, h=65)

    create_entity(r2_2, 'e3_stud', 'STUDENT', 'Enrolled Payer', x=100, y=240, w=180, h=100)
    create_entity(r2_2, 'e3_adm',  'ADMIN / TREASURER', 'Payment Verifier', x=100, y=620, w=180, h=100)
    create_entity(r2_2, 'e3_cloud', 'CLOUDINARY / STORAGE', 'External Storage Service', x=950, y=180, w=220, h=80)

    create_process(r2_2, 'p3_2_1', '2.1', 'Fee Amount &amp; GCash<br>Details Retrieval', x=460, y=180, w=220, h=80)
    create_process(r2_2, 'p3_2_2', '2.2', 'Payment Proof Upload<br>&amp; Cloudinary Storage', x=460, y=360, w=220, h=80)
    create_process(r2_2, 'p3_2_3', '2.3', 'Admin Verification<br>&amp; Clearance Status Update', x=460, y=560, w=220, h=80)

    create_datastore(r2_2, 'ds3_d3', 'D3', 'enrollment_payments', 'id, user_id, amount, payment_method, proof_file, proof_status, notes', x=950, y=420, w=370, h=75)

    create_flow(r2_2, 'f3_2_1', 'e3_stud', 'p3_2_1', 'Fetch Payment Instructions', sx=1.0, sy=0.30, ex=0.0, ey=0.50, points=[(340, 270), (340, 220)])
    create_flow(r2_2, 'f3_2_2', 'p3_2_1', 'e3_stud', 'Display Fee (P50) &amp; GCash QR / Number', sx=0.0, sy=0.80, ex=1.0, ey=0.60, points=[(380, 244), (380, 300)])
    create_flow(r2_2, 'f3_2_3', 'e3_stud', 'p3_2_2', 'Submit: {amount, gcash_ref, proof_image}', sx=0.75, sy=1.0, ex=0.0, ey=0.35, points=[(235, 388)])
    create_flow(r2_2, 'f3_2_4', 'p3_2_2', 'e3_cloud', 'POST image binary via Cloudinary SDK', sx=1.0, sy=0.30, ex=0.0, ey=0.50, points=[(780, 384), (780, 220)])
    create_flow(r2_2, 'f3_2_5', 'e3_cloud', 'p3_2_2', 'Return secure Cloudinary HTTPS URL', sx=0.25, sy=1.0, ex=0.85, ey=0.0, points=[(1005, 310), (647, 310)])
    create_flow(r2_2, 'f3_2_6', 'p3_2_2', 'ds3_d3', 'INSERT INTO enrollment_payments (status="pending")', sx=1.0, sy=0.75, ex=0.0, ey=0.35, points=[(780, 420), (780, 446)])
    create_flow(r2_2, 'f3_2_7', 'ds3_d3', 'p3_2_3', 'Fetch pending proofs for verification queue', sx=0.0, sy=0.75, ex=1.0, ey=0.35, points=[(820, 476), (820, 588)])
    create_flow(r2_2, 'f3_2_8', 'e3_adm', 'p3_2_3', 'Verify Decision: {payment_id, action: "paid"|"rejected"}', sx=1.0, sy=0.50, ex=0.0, ey=0.65, points=[(360, 670), (360, 612)])
    create_flow(r2_2, 'f3_2_9', 'p3_2_3', 'ds3_d3', 'UPDATE enrollment_payments SET proof_status=...', sx=0.85, sy=0.0, ex=0.50, ey=1.0, points=[(647, 525), (1135, 525)])
    create_flow(r2_2, 'f3_2_10', 'p3_2_3', 'e3_stud', 'Notify Student: "Paid &amp; Cleared"', sx=0.0, sy=0.20, ex=0.25, ey=1.0, points=[(320, 576), (320, 400), (145, 400)])


    # ══════════════════════════════════════════════════════════════════════════
    # PAGE 5: LEVEL 2 - 3.0 BUDGET ALLOCATION & FISCAL SETUP
    # ══════════════════════════════════════════════════════════════════════════
    d2_3 = ET.SubElement(mxfile, 'diagram', {'id': 'dfd-level-2-budget', 'name': 'Level 2 - 3.0 Budget Allocation'})
    m2_3 = ET.SubElement(d2_3, 'mxGraphModel', {'dx': '1800', 'dy': '1200', 'grid': '0', 'page': '1', 'pageWidth': '1800', 'pageHeight': '1200'})
    r2_3 = ET.SubElement(m2_3, 'root')
    add_cell(r2_3, id='0')
    add_cell(r2_3, id='1', parent='0')

    create_title(r2_3, 't2_3', 'Level 2 – 3.0 Budget Allocation & Fiscal Oversight',
                 'Detailed data flow for school year configuration, department allocations & ledger audits',
                 x=300, y=30, w=1200, h=65)

    create_entity(r2_3, 'e4_adm',   'ADMIN / ADVISER', 'Budget Controller', x=100, y=240, w=180, h=100)
    create_entity(r2_3, 'e4_treas', 'SSC TREASURER', 'Council Disburser', x=100, y=560, w=180, h=100)

    create_process(r2_3, 'p4_3_1', '3.1', 'Fiscal Year Activation<br>&amp; Election Window Setup', x=460, y=180, w=220, h=80)
    create_process(r2_3, 'p4_3_2', '3.2', 'Department Allocation<br>&amp; Cap Definition', x=460, y=340, w=220, h=80)
    create_process(r2_3, 'p4_3_3', '3.3', 'Real-time Balance Audit<br>&amp; Financial Summary', x=460, y=520, w=220, h=80)

    create_datastore(r2_3, 'ds4_sy', 'D15', 'school_years', 'id, year, semester, is_active, voting_open', x=950, y=185, w=350, h=65)
    create_datastore(r2_3, 'ds4_d4', 'D4', 'budgets', 'id, title, department, allocated_amount, remaining_balance', x=950, y=380, w=350, h=70)

    create_flow(r2_3, 'f4_3_1', 'e4_adm', 'p4_3_1', 'Create/Activate Year: {year: "2026-2027", sem: 1}', sx=1.0, sy=0.30, ex=0.0, ey=0.50, points=[(340, 270), (340, 220)])
    create_flow(r2_3, 'f4_3_2', 'p4_3_1', 'ds4_sy', 'INSERT / UPDATE school_years (is_active=1)', sx=1.0, sy=0.50, ex=0.0, ey=0.50)
    create_flow(r2_3, 'f4_3_3', 'e4_adm', 'p4_3_2', 'New Allocation: {department, allocated_amount}', sx=1.0, sy=0.75, ex=0.0, ey=0.35, points=[(380, 315), (380, 368)])
    create_flow(r2_3, 'f4_3_4', 'p4_3_2', 'ds4_d4', 'INSERT INTO budgets: {allocated_amount, remaining_balance}', sx=1.0, sy=0.50, ex=0.0, ey=0.35, points=[(780, 380), (780, 404)])
    create_flow(r2_3, 'f4_3_5', 'ds4_d4', 'p4_3_3', 'Fetch live allocation balances &amp; remaining funds', sx=0.0, sy=0.75, ex=1.0, ey=0.35, points=[(820, 432), (820, 548)])
    create_flow(r2_3, 'f4_3_6', 'p4_3_3', 'e4_treas', 'Department Budget Summaries &amp; Disbursement Limits', sx=0.0, sy=0.50, ex=1.0, ey=0.35, points=[(360, 560), (360, 595)])
    create_flow(r2_3, 'f4_3_7', 'p4_3_3', 'e4_adm', 'Consolidated Executive Financial PDF/Excel Report', sx=0.0, sy=0.80, ex=0.50, ey=1.0, points=[(320, 584), (320, 460), (190, 460), (190, 340)])


    # ══════════════════════════════════════════════════════════════════════════
    # PAGE 6: LEVEL 2 - 4.0 PROPOSALS, RELEASES, EXPENSES & LIQUIDATION
    # ══════════════════════════════════════════════════════════════════════════
    d2_4 = ET.SubElement(mxfile, 'diagram', {'id': 'dfd-level-2-proposals', 'name': 'Level 2 - 4.0 Proposals & Finance'})
    m2_4 = ET.SubElement(d2_4, 'mxGraphModel', {'dx': '2000', 'dy': '1400', 'grid': '0', 'page': '1', 'pageWidth': '2000', 'pageHeight': '1400'})
    r2_4 = ET.SubElement(m2_4, 'root')
    add_cell(r2_4, id='0')
    add_cell(r2_4, id='1', parent='0')

    create_title(r2_4, 't2_4', 'Level 2 – 4.0 Project Proposals, Releases, Expenses & Liquidation',
                 'Detailed financial lifecycle from project proposal submission to fund release, expense logs and liquidation audit',
                 x=400, y=30, w=1200, h=65)

    create_entity(r2_4, 'e5_off',   'SSC OFFICER', 'Proponent &amp; Expense Logger', x=100, y=280, w=180, h=100)
    create_entity(r2_4, 'e5_adm',   'ADMIN / ADVISER', 'Proposal Reviewer &amp; Auditor', x=100, y=660, w=180, h=100)
    create_entity(r2_4, 'e5_treas', 'SSC TREASURER', 'Fund Release Disburser', x=100, y=1020, w=180, h=100)

    create_process(r2_4, 'p5_4_1', '4.1', 'Proposal Submission<br>&amp; Itemized Estimates', x=500, y=180, w=220, h=80)
    create_process(r2_4, 'p5_4_2', '4.2', 'Admin Proposal Review<br>&amp; Grant Allocation', x=500, y=380, w=220, h=80)
    create_process(r2_4, 'p5_4_3', '4.3', 'Treasurer Fund Release<br>&amp; Balance Deduction', x=500, y=580, w=220, h=80)
    create_process(r2_4, 'p5_4_4', '4.4', 'Expense Receipt Logging<br>&amp; Claim Submission', x=500, y=780, w=220, h=80)
    create_process(r2_4, 'p5_4_5', '4.5', 'Post-Event Liquidation<br>&amp; Settlement Audit', x=500, y=980, w=220, h=80)

    create_datastore(r2_4, 'ds5_d5', 'D5', 'proposals', 'id, officer_id, title, requested_budget, approved_budget, status', x=1020, y=240, w=360, h=70)
    create_datastore(r2_4, 'ds5_d6', 'D6', 'budget_releases', 'id, budget_id, proposal_id, amount, released_at', x=1020, y=480, w=360, h=65)
    create_datastore(r2_4, 'ds5_d4', 'D4', 'budgets', 'id, remaining_balance = remaining_balance - release_amount', x=1020, y=620, w=360, h=65)
    create_datastore(r2_4, 'ds5_d7', 'D7', 'expenses', 'id, budget_id, officer_id, title, amount, receipt_path', x=1020, y=790, w=360, h=65)
    create_datastore(r2_4, 'ds5_d8', 'D8', 'liquidations', 'id, proposal_id, officer_id, report_document, status', x=1020, y=990, w=360, h=65)

    create_flow(r2_4, 'f5_4_1', 'e5_off', 'p5_4_1', 'Submit Proposal Form: {title, requested_budget, items}', sx=1.0, sy=0.25, ex=0.0, ey=0.50, points=[(360, 305), (360, 220)])
    create_flow(r2_4, 'f5_4_2', 'p5_4_1', 'ds5_d5', 'INSERT INTO proposals (status="Pending")', sx=1.0, sy=0.50, ex=0.0, ey=0.35, points=[(840, 220), (840, 264)])
    create_flow(r2_4, 'f5_4_3', 'ds5_d5', 'p5_4_2', 'Fetch pending proposal for review', sx=0.0, sy=0.75, ex=1.0, ey=0.30, points=[(900, 292), (900, 404)])
    create_flow(r2_4, 'f5_4_4', 'e5_adm', 'p5_4_2', 'Review: {approved_budget, decision: "Approved"|"Rejected"}', sx=1.0, sy=0.25, ex=0.0, ey=0.50, points=[(360, 685), (360, 420)])
    create_flow(r2_4, 'f5_4_5', 'p5_4_2', 'ds5_d5', 'UPDATE proposals SET approved_budget=..., status="Approved"', sx=0.75, sy=0.0, ex=0.25, ey=1.0, points=[(665, 340), (1110, 340), (1110, 310)])
    create_flow(r2_4, 'f5_4_6', 'e5_treas', 'p5_4_3', 'Authorize Fund Release: {proposal_id, amount}', sx=1.0, sy=0.25, ex=0.0, ey=0.50, points=[(360, 1045), (360, 620)])
    create_flow(r2_4, 'f5_4_7', 'p5_4_3', 'ds5_d6', 'INSERT INTO budget_releases: {amount, released_at}', sx=1.0, sy=0.35, ex=0.0, ey=0.50, points=[(840, 608), (840, 512)])
    create_flow(r2_4, 'f5_4_8', 'p5_4_3', 'ds5_d4', 'UPDATE budgets SET remaining_balance = remaining - amount', sx=1.0, sy=0.70, ex=0.0, ey=0.50, points=[(840, 636), (840, 652)])
    create_flow(r2_4, 'f5_4_9', 'e5_off', 'p5_4_4', 'Log Expense Claim: {budget_id, title, amount, receipt}', sx=1.0, sy=0.60, ex=0.0, ey=0.35, points=[(320, 340), (320, 808)])
    create_flow(r2_4, 'f5_4_10', 'p5_4_4', 'ds5_d7', 'INSERT INTO expenses: {officer_id, amount, receipt_path}', sx=1.0, sy=0.50, ex=0.0, ey=0.50)
    create_flow(r2_4, 'f5_4_11', 'e5_off', 'p5_4_5', 'Submit Liquidation Form: {proposal_id, report_file}', sx=1.0, sy=0.85, ex=0.0, ey=0.35, points=[(280, 365), (280, 1008)])
    create_flow(r2_4, 'f5_4_12', 'p5_4_5', 'ds5_d8', 'INSERT INTO liquidations: {status="Submitted"}', sx=1.0, sy=0.50, ex=0.0, ey=0.50)
    create_flow(r2_4, 'f5_4_13', 'e5_adm', 'p5_4_5', 'Audit Liquidation: {liquidation_id, action: "Settled"}', sx=1.0, sy=0.75, ex=0.0, ey=0.70, points=[(340, 735), (340, 1036)])


    # ══════════════════════════════════════════════════════════════════════════
    # PAGE 7: LEVEL 2 - 5.0 CANDIDACY FILING & SECRET BALLOT ELECTION
    # ══════════════════════════════════════════════════════════════════════════
    d2_5 = ET.SubElement(mxfile, 'diagram', {'id': 'dfd-level-2-election', 'name': 'Level 2 - 5.0 Candidacy & Election'})
    m2_5 = ET.SubElement(d2_5, 'mxGraphModel', {'dx': '2000', 'dy': '1400', 'grid': '0', 'page': '1', 'pageWidth': '2000', 'pageHeight': '1400'})
    r2_5 = ET.SubElement(m2_5, 'root')
    add_cell(r2_5, id='0')
    add_cell(r2_5, id='1', parent='0')

    create_title(r2_5, 't2_5', 'Level 2 – 5.0 Candidacy Filing & Secret Ballot Online Election',
                 'Detailed data flow for candidate filing, dean vetting, digital ballot voting & automated live tallying',
                 x=400, y=30, w=1200, h=65)

    create_entity(r2_5, 'e6_stud', 'STUDENT VOTER / CANDIDATE', 'Student Body', x=100, y=280, w=200, h=120)
    create_entity(r2_5, 'e6_dean', 'COLLEGE DEAN', 'Department Evaluator', x=100, y=620, w=200, h=90)
    create_entity(r2_5, 'e6_adm',  'SSC ADMIN / ELECOM', 'Electoral Commission', x=100, y=920, w=200, h=90)

    create_process(r2_5, 'p6_5_1', '5.1', 'Candidacy Application<br>&amp; Requirements Filing', x=500, y=180, w=220, h=80)
    create_process(r2_5, 'p6_5_2', '5.2', 'Dean Electoral Evaluation<br>&amp; Slate Endorsement', x=500, y=380, w=220, h=80)
    create_process(r2_5, 'p6_5_3', '5.3', 'Ballot Initialization<br>&amp; Anti-Duplicate Check', x=500, y=580, w=220, h=80)
    create_process(r2_5, 'p6_5_4', '5.4', 'Secret Ballot Voting<br>&amp; Cryptographic Storage', x=500, y=780, w=220, h=80)
    create_process(r2_5, 'p6_5_5', '5.5', 'Automated Vote Tallying<br>&amp; Certification', x=500, y=980, w=220, h=80)

    create_datastore(r2_5, 'ds6_sy',  'D15', 'school_years', 'id, is_active, voting_open, candidacy_open', x=1020, y=180, w=360, h=65)
    create_datastore(r2_5, 'ds6_d9',  'D9',  'candidacies', 'id, user_id, position, platform, photo, dean_status, status', x=1020, y=380, w=360, h=70)
    create_datastore(r2_5, 'ds6_d10', 'D10', 'student_ballots', 'id, user_id, school_year_id, status="completed", submitted_at', x=1020, y=620, w=360, h=65)
    create_datastore(r2_5, 'ds6_d11', 'D11', 'votes', 'id, user_id, candidacy_id, position, is_skip, created_at', x=1020, y=820, w=360, h=65)

    create_flow(r2_5, 'f6_5_1', 'e6_stud', 'p6_5_1', 'Submit Application: {position, platform, photo}', sx=1.0, sy=0.25, ex=0.0, ey=0.50, points=[(360, 310), (360, 220)])
    create_flow(r2_5, 'f6_5_2', 'ds6_sy', 'p6_5_1', 'Verify candidacy_open == true', sx=0.0, sy=0.50, ex=1.0, ey=0.35, points=[(880, 212), (880, 208)])
    create_flow(r2_5, 'f6_5_3', 'p6_5_1', 'ds6_d9', 'INSERT INTO candidacies (status="pending")', sx=1.0, sy=0.70, ex=0.0, ey=0.35, points=[(840, 236), (840, 404)])
    create_flow(r2_5, 'f6_5_4', 'ds6_d9', 'p6_5_2', 'Fetch pending department candidates', sx=0.0, sy=0.70, ex=1.0, ey=0.35, points=[(880, 429), (880, 408)])
    create_flow(r2_5, 'f6_5_5', 'e6_dean', 'p6_5_2', 'Dean Decision: {candidacy_id, vote: approve/reject}', sx=1.0, sy=0.50, ex=0.0, ey=0.50, points=[(360, 665), (360, 420)])
    create_flow(r2_5, 'f6_5_6', 'p6_5_2', 'ds6_d9', 'UPDATE candidacies SET dean_status="approved"', sx=0.75, sy=0.0, ex=0.50, ey=1.0, points=[(665, 340), (1200, 340), (1200, 450)])
    create_flow(r2_5, 'f6_5_7', 'e6_stud', 'p6_5_3', 'Open Voting Session: {user_id}', sx=1.0, sy=0.60, ex=0.0, ey=0.35, points=[(340, 352), (340, 608)])
    create_flow(r2_5, 'f6_5_8', 'p6_5_3', 'ds6_d10', 'Check prior ballot &amp; INSERT in_progress', sx=1.0, sy=0.50, ex=0.0, ey=0.35, points=[(840, 620), (840, 642)])
    create_flow(r2_5, 'f6_5_9', 'p6_5_3', 'e6_stud', 'Render Interactive Digital Ballot', sx=0.0, sy=0.75, ex=1.0, ey=0.80, points=[(380, 640), (380, 376)])
    create_flow(r2_5, 'f6_5_10', 'e6_stud', 'p6_5_4', 'Cast Ballot: {candidacy_id per position, or skip}', sx=0.50, sy=1.0, ex=0.0, ey=0.35, points=[(200, 808)])
    create_flow(r2_5, 'f6_5_11', 'p6_5_4', 'ds6_d11', 'INSERT INTO votes {candidacy_id, position}', sx=1.0, sy=0.50, ex=0.0, ey=0.50)
    create_flow(r2_5, 'f6_5_12', 'p6_5_4', 'ds6_d10', 'UPDATE student_ballots SET status="completed"', sx=0.75, sy=0.0, ex=0.50, ey=1.0, points=[(665, 740), (1200, 740), (1200, 685)])
    create_flow(r2_5, 'f6_5_13', 'e6_adm', 'p6_5_5', 'Elecom Action: Close Election &amp; Certify', sx=1.0, sy=0.50, ex=0.0, ey=0.50, points=[(360, 965), (360, 1020)])
    create_flow(r2_5, 'f6_5_14', 'ds6_d11', 'p6_5_5', 'COUNT(votes) GROUP BY candidacy_id, position', sx=0.0, sy=0.75, ex=1.0, ey=0.35, points=[(880, 868), (880, 1008)])
    create_flow(r2_5, 'f6_5_15', 'p6_5_5', 'e6_stud', 'Certified Official Election Winners &amp; Tally', sx=0.0, sy=0.85, ex=0.25, ey=1.0, points=[(320, 1048), (320, 480), (150, 480), (150, 400)])


    # ══════════════════════════════════════════════════════════════════════════
    # PAGE 8: LEVEL 2 - 6.0 ANNOUNCEMENTS, FEEDBACK & AUDIT LOGS
    # ══════════════════════════════════════════════════════════════════════════
    d2_6 = ET.SubElement(mxfile, 'diagram', {'id': 'dfd-level-2-announcements', 'name': 'Level 2 - 6.0 Announcements & Feedback'})
    m2_6 = ET.SubElement(d2_6, 'mxGraphModel', {'dx': '2000', 'dy': '1400', 'grid': '0', 'page': '1', 'pageWidth': '2000', 'pageHeight': '1400'})
    r2_6 = ET.SubElement(m2_6, 'root')
    add_cell(r2_6, id='0')
    add_cell(r2_6, id='1', parent='0')

    create_title(r2_6, 't2_6', 'Level 2 – 6.0 Announcements, Community Feedback & Audit Logs',
                 'Detailed data flow for public broadcasts, lost & found discussions, grievance handling & system activity logging',
                 x=400, y=30, w=1200, h=65)

    create_entity(r2_6, 'e7_adm',  'ADMIN / SSC OFFICER', 'Publisher &amp; Responder', x=100, y=260, w=200, h=100)
    create_entity(r2_6, 'e7_stud', 'STUDENT COMMUNITY', 'Reader &amp; Participant', x=100, y=660, w=200, h=120)

    create_process(r2_6, 'p7_6_1', '6.1', 'Announcement Publishing<br>&amp; Category Tagging', x=500, y=180, w=220, h=80)
    create_process(r2_6, 'p7_6_2', '6.2', 'Lost &amp; Found Item Feed<br>&amp; Discussion Threading', x=500, y=380, w=220, h=80)
    create_process(r2_6, 'p7_6_3', '6.3', 'Student Grievance &amp;<br>Feedback Ingestion', x=500, y=580, w=220, h=80)
    create_process(r2_6, 'p7_6_4', '6.4', 'Admin Response Dispatch<br>&amp; Ticket Resolution', x=500, y=780, w=220, h=80)
    create_process(r2_6, 'p7_6_5', '6.5', 'Automated Push Broadcast<br>&amp; Audit Trail Logging', x=500, y=980, w=220, h=80)

    create_datastore(r2_6, 'ds7_d12',  'D12',   'announcements', 'id, title, content, category, image_file, project_id', x=1020, y=200, w=360, h=70)
    create_datastore(r2_6, 'ds7_d12c', 'D12.1', 'announcement_comments', 'id, announcement_id, user_id, comment, created_at', x=1020, y=380, w=360, h=65)
    create_datastore(r2_6, 'ds7_d13',  'D13',   'feedback', 'id, student_id, message, reply, status, replied_by', x=1020, y=680, w=360, h=70)
    create_datastore(r2_6, 'ds7_d14',  'D14',   'activity_logs', 'id, user_id, action, details, ip_address, created_at', x=1020, y=980, w=360, h=65)

    create_flow(r2_6, 'f7_6_1', 'e7_adm', 'p7_6_1', 'Publish: {title, content, category, image}', sx=1.0, sy=0.30, ex=0.0, ey=0.50, points=[(360, 290), (360, 220)])
    create_flow(r2_6, 'f7_6_2', 'p7_6_1', 'ds7_d12', 'INSERT INTO announcements', sx=1.0, sy=0.50, ex=0.0, ey=0.50)
    create_flow(r2_6, 'f7_6_3', 'ds7_d12', 'p7_6_2', 'Fetch live feed (News, Lost &amp; Found)', sx=0.0, sy=0.75, ex=1.0, ey=0.30, points=[(900, 252), (900, 404)])
    create_flow(r2_6, 'f7_6_4', 'p7_6_2', 'e7_stud', 'Display announcements &amp; completion showcase', sx=0.0, sy=0.75, ex=1.0, ey=0.25, points=[(360, 440), (360, 690)])
    create_flow(r2_6, 'f7_6_5', 'e7_stud', 'p7_6_2', 'Comment on Item: {announcement_id, comment}', sx=1.0, sy=0.45, ex=0.0, ey=0.50, points=[(420, 714), (420, 420)])
    create_flow(r2_6, 'f7_6_6', 'p7_6_2', 'ds7_d12c', 'INSERT INTO announcement_comments', sx=1.0, sy=0.50, ex=0.0, ey=0.50)
    create_flow(r2_6, 'f7_6_7', 'e7_stud', 'p7_6_3', 'Submit Feedback: {student_id, message}', sx=1.0, sy=0.70, ex=0.0, ey=0.50, points=[(380, 744), (380, 620)])
    create_flow(r2_6, 'f7_6_8', 'p7_6_3', 'ds7_d13', 'INSERT INTO feedback (status="Pending")', sx=1.0, sy=0.50, ex=0.0, ey=0.35, points=[(840, 620), (840, 704)])
    create_flow(r2_6, 'f7_6_9', 'ds7_d13', 'p7_6_4', 'Fetch pending tickets for Admin inbox', sx=0.0, sy=0.70, ex=1.0, ey=0.35, points=[(900, 729), (900, 808)])
    create_flow(r2_6, 'f7_6_10', 'e7_adm', 'p7_6_4', 'Submit Reply: {feedback_id, reply_text}', sx=1.0, sy=0.75, ex=0.0, ey=0.50, points=[(320, 335), (320, 820)])
    create_flow(r2_6, 'f7_6_11', 'p7_6_4', 'ds7_d13', 'UPDATE feedback SET status="Replied", reply=...', sx=1.0, sy=0.75, ex=0.0, ey=0.85, points=[(840, 840), (840, 739)])
    create_flow(r2_6, 'f7_6_12', 'p7_6_4', 'e7_stud', 'Notify Student: Admin reply visible in portal', sx=0.0, sy=0.80, ex=1.0, ey=0.85, points=[(360, 844), (360, 762)])
    create_flow(r2_6, 'f7_6_13', 'p7_6_1', 'p7_6_5', 'Trigger push notification broadcast', sx=0.25, sy=1.0, ex=0.25, ey=0.0, points=[(555, 300), (460, 300), (460, 940), (555, 940)])
    create_flow(r2_6, 'f7_6_14', 'p7_6_5', 'ds7_d14', 'INSERT INTO activity_logs (action, details, ip)', sx=1.0, sy=0.50, ex=0.0, ey=0.50)

    # ── Write file ────────────────────────────────────────────────────────────
    tree = ET.ElementTree(mxfile)
    ET.indent(tree, space='  ', level=0)
    out_path = os.path.abspath('docs/SSC_DFD.drawio')
    tree.write(out_path, encoding='utf-8', xml_declaration=False)
    print(f"[SUCCESS] Complete Pure Black & White DFD generated at: {out_path}")

if __name__ == '__main__':
    build_complete_dfd()
