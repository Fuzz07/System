import xml.etree.ElementTree as ET
import os

def generate_complete_dfd_all_levels():
    mxfile = ET.Element('mxfile', {'host': 'Electron', 'agent': 'Antigravity IDE', 'version': '21.0.0'})

    # Helper styling functions
    def add_title(root, tid, text, subtitle, x=500, y=30, w=1200, h=65):
        cell = ET.SubElement(root, 'mxCell', {
            'id': tid, 'parent': '1', 'vertex': '1',
            'style': 'text;html=1;whiteSpace=wrap;align=center;verticalAlign=middle;fontFamily=Helvetica;fontStyle=1;fontSize=18;fillColor=#0F172A;fontColor=#FFFFFF;rounded=1;strokeColor=#334155;strokeWidth=1;',
            'value': f'{text}<br><span style="font-size:12px;font-weight:normal;color:#94A3B8;">{subtitle}</span>'
        })
        ET.SubElement(cell, 'mxGeometry', {'x': str(x), 'y': str(y), 'width': str(w), 'height': str(h), 'as': 'geometry'})
        return cell

    def add_process(root, pid, label, x, y, w=200, h=85, color='#2563EB', stroke='#1D4ED8'):
        cell = ET.SubElement(root, 'mxCell', {
            'id': pid, 'parent': '1', 'vertex': '1',
            'style': f'rounded=1;arcSize=24;whiteSpace=wrap;html=1;fillColor={color};strokeColor={stroke};strokeWidth=2;fontColor=#FFFFFF;fontFamily=Helvetica;align=center;verticalAlign=middle;fontStyle=1;fontSize=11;',
            'value': label
        })
        ET.SubElement(cell, 'mxGeometry', {'x': str(x), 'y': str(y), 'width': str(w), 'height': str(h), 'as': 'geometry'})
        return cell

    def add_entity(root, eid, label, x, y, w=180, h=75, color='#1E293B', stroke='#0F172A'):
        cell = ET.SubElement(root, 'mxCell', {
            'id': eid, 'parent': '1', 'vertex': '1',
            'style': f'rounded=0;whiteSpace=wrap;html=1;fillColor={color};strokeColor={stroke};strokeWidth=2;fontColor=#FFFFFF;fontFamily=Helvetica;align=center;verticalAlign=middle;fontStyle=1;fontSize=12;',
            'value': label
        })
        ET.SubElement(cell, 'mxGeometry', {'x': str(x), 'y': str(y), 'width': str(w), 'height': str(h), 'as': 'geometry'})
        return cell

    def add_datastore(root, did, label, x, y, w=260, h=65, color='#F8FAFC', stroke='#64748B'):
        cell = ET.SubElement(root, 'mxCell', {
            'id': did, 'parent': '1', 'vertex': '1',
            'style': f'shape=partialRectangle;top=0;bottom=0;fillColor={color};strokeColor={stroke};strokeWidth=2;fontColor=#0F172A;fontFamily=Helvetica;align=left;spacingLeft=10;verticalAlign=middle;fontSize=10;',
            'value': label
        })
        ET.SubElement(cell, 'mxGeometry', {'x': str(x), 'y': str(y), 'width': str(w), 'height': str(h), 'as': 'geometry'})
        return cell

    def add_flow(root, fid, src, trg, label, color='#334155'):
        cell = ET.SubElement(root, 'mxCell', {
            'id': fid, 'parent': '1', 'edge': '1', 'source': src, 'target': trg,
            'style': f'edgeStyle=orthogonalEdgeStyle;rounded=1;orthogonalLoop=1;jettySize=auto;html=1;strokeColor={color};strokeWidth=2;fontSize=10;fontFamily=Helvetica;fontColor=#0F172A;align=center;verticalAlign=bottom;labelBackgroundColor=#FFFFFF;spacing=2;',
            'value': label
        })
        ET.SubElement(cell, 'mxGeometry', {'relative': '1', 'as': 'geometry'})
        return cell

    # =========================================================================
    # TAB 1: LEVEL 0 - Context Diagram
    # =========================================================================
    d0 = ET.SubElement(mxfile, 'diagram', {'id': 'dfd-level-0', 'name': 'Level 0 - Context Diagram'})
    m0 = ET.SubElement(d0, 'mxGraphModel', {'dx': '2200', 'dy': '1400', 'grid': '1', 'gridSize': '10', 'page': '1', 'pageWidth': '2200', 'pageHeight': '1400'})
    r0 = ET.SubElement(m0, 'root')
    ET.SubElement(r0, 'mxCell', {'id': '0'})
    ET.SubElement(r0, 'mxCell', {'id': '1', 'parent': '0'})

    add_title(r0, 't0', 'SSC BUDGET &amp; GOVERNANCE MANAGEMENT SYSTEM', 'Level 0 Context Diagram - Core System Boundary and External Interfaces', 500, 30, 1200, 60)

    p0 = ET.SubElement(r0, 'mxCell', {
        'id': 'p0', 'parent': '1', 'vertex': '1',
        'style': 'ellipse;whiteSpace=wrap;html=1;aspect=fixed;fillColor=#1E40AF;strokeColor=#1D4ED8;strokeWidth=3;fontColor=#FFFFFF;fontFamily=Helvetica;align=center;verticalAlign=middle;fontStyle=1;fontSize=15;',
        'value': '<b>0.0</b><br><br><b style="font-size:17px;">SSC Budget &amp;<br>Governance System</b><br><br><span style="font-size:11px;font-weight:normal;color:#DBEAFE;">(Core Backend, Web &amp; Mobile Portals)</span>'
    })
    ET.SubElement(p0, 'mxGeometry', {'x': '950', 'y': '480', 'width': '300', 'height': '300', 'as': 'geometry'})

    add_entity(r0, 'e_stud', 'External Entity<br><b style="font-size:15px;">STUDENT</b><br><span style="font-size:10px;font-weight:normal;color:#93C5FD;">(Web Portal / Mobile PWA)</span>', 80, 240, 220, 90, '#1E3A8A')
    add_entity(r0, 'e_off', 'External Entity<br><b style="font-size:15px;">SSC OFFICER</b><br><span style="font-size:10px;font-weight:normal;color:#C4B5FD;">(Proposal Proponent)</span>', 80, 850, 220, 90, '#5B21B6')
    add_entity(r0, 'e_treas', 'External Entity<br><b style="font-size:15px;">SSC TREASURER</b><br><span style="font-size:10px;font-weight:normal;color:#6EE7B7;">(Disbursing Officer)</span>', 1900, 850, 220, 90, '#065F46')
    add_entity(r0, 'e_adm', 'External Entity<br><b style="font-size:15px;">SSC ADMIN / ADVISER</b><br><span style="font-size:10px;font-weight:normal;color:#FCA5A5;">(System Administrator)</span>', 1900, 240, 220, 90, '#991B1B')
    add_entity(r0, 'e_dean', 'External Entity<br><b style="font-size:15px;">COLLEGE DEAN</b><br><span style="font-size:10px;font-weight:normal;color:#FDE68A;">(Electoral Endorser)</span>', 990, 1150, 220, 85, '#92400E')

    add_flow(r0, 'f0_1', 'e_stud', 'p0', '<b>Student Submissions:</b><br>• Registration: {student_id, first_name, middle_name, last_name, email, password, department, year_level}<br>• Login Credentials: {email, password}<br>• Enrollment Fee Proof: {payment_method, proof_file (image/pdf/mp4), amount}<br>• Candidacy Filing: {position, platform, photo_file}<br>• Secret Ballot: {candidacy_id, position, is_skip}<br>• Feedback &amp; Comments: {message, comment}', '#2563EB')
    add_flow(r0, 'f0_2', 'p0', 'e_stud', '<b>Student Feeds &amp; Responses:</b><br>• Account Status &amp; Auth Tokens<br>• Payment Reference Code &amp; Proof Verification Status (pending/approved/rejected)<br>• Official Announcements &amp; Approved Proposals<br>• Active Ballot Interface &amp; Certified Election Results<br>• Push Notifications &amp; Admin Feedback Replies', '#1D4ED8')
    add_flow(r0, 'f0_3', 'e_off', 'p0', '<b>Officer Submissions:</b><br>• Project Proposal: {project_title, requested_budget, description}<br>• Expense Claims: {budget_id, expense_title, amount, receipt_file, description}<br>• Project Liquidation: {proposal_id, title, file_path, notes}<br>• Completion Proof: {completion_proof_file, status}', '#7C3AED')
    add_flow(r0, 'f0_4', 'p0', 'e_off', '<b>Officer Feeds &amp; Reports:</b><br>• Department Budget Allocation &amp; Balance<br>• Proposal Status (Pending/Approved/Rejected) &amp; Notes<br>• Disbursement Verification &amp; Expense Approvals<br>• Student Comments on Projects', '#6D28D9')
    add_flow(r0, 'f0_5', 'e_treas', 'p0', '<b>Disbursement Actions:</b><br>• Budget Release: {budget_id, proposal_id, amount, receipt_file, notes, released_at}', '#059669')
    add_flow(r0, 'f0_6', 'p0', 'e_treas', '<b>Financial Ledgers:</b><br>• Approved Budget Lines &amp; Real-time Balances<br>• Approved Proposals Ready for Release<br>• Financial Reports &amp; Liquidation Records', '#047857')
    add_flow(r0, 'f0_7', 'e_adm', 'p0', '<b>Admin Controls &amp; Governance:</b><br>• Eligible Roster CSV: {student_id, fullname, department, year_level}<br>• Budget Setup: {title, department, allocated_amount, school_year}<br>• Payment Proof Verification: {payment_id, proof_status, proof_notes}<br>• Proposal Review: {proposal_id, approved_budget, status, admin_notes}<br>• Election Lifecycle: {candidacy_open, voting_open, announce_results}<br>• Announcements: {title, content, category, image_file}', '#DC2626')
    add_flow(r0, 'f0_8', 'p0', 'e_adm', '<b>Governance Feeds &amp; Analytics:</b><br>• Whitelist &amp; Registered Student Directory<br>• Payment Verification Queue &amp; Walk-in Receipts<br>• Real-time Budget vs Expense Audit<br>• Election Tally Audits &amp; Activity Logs', '#B91C1C')
    add_flow(r0, 'f0_9', 'e_dean', 'p0', '<b>Dean Votes/Endorsements:</b><br>{candidacy_id, vote_decision, rejection_notes}', '#D97706')
    add_flow(r0, 'f0_10', 'p0', 'e_dean', '<b>Department Feeds:</b><br>• Candidate Profiles in Department<br>• Live / Certified Election Results', '#B45309')

    # =========================================================================
    # TAB 2: LEVEL 1 - System Decomposition
    # =========================================================================
    d1 = ET.SubElement(mxfile, 'diagram', {'id': 'dfd-level-1', 'name': 'Level 1 - Core Subsystems'})
    m1 = ET.SubElement(d1, 'mxGraphModel', {'dx': '2600', 'dy': '1800', 'grid': '1', 'gridSize': '10', 'page': '1', 'pageWidth': '2600', 'pageHeight': '1800'})
    r1 = ET.SubElement(m1, 'root')
    ET.SubElement(r1, 'mxCell', {'id': '0'})
    ET.SubElement(r1, 'mxCell', {'id': '1', 'parent': '0'})

    add_title(r1, 't1', 'SSC BUDGET &amp; GOVERNANCE MANAGEMENT SYSTEM', 'Level 1 Data Flow Diagram - Subsystem Decomposition &amp; Global Store Routing', 700, 30, 1200, 60)

    add_entity(r1, 'l1_e_stud', 'STUDENT', 50, 220, 160, 70, '#1E3A8A')
    add_entity(r1, 'l1_e_off', 'SSC OFFICER', 50, 650, 160, 70, '#5B21B6')
    add_entity(r1, 'l1_e_treas', 'SSC TREASURER', 50, 1050, 160, 70, '#065F46')
    add_entity(r1, 'l1_e_adm', 'SSC ADMIN / ADVISER', 50, 1420, 160, 70, '#991B1B')
    add_entity(r1, 'l1_e_dean', 'COLLEGE DEAN', 50, 1650, 160, 70, '#92400E')

    add_process(r1, 'p1_1', '<b>1.0</b><br>User Management, Auth<br>&amp; Whitelist Verification', 420, 180, 230, 90, '#2563EB', '#1D4ED8')
    add_process(r1, 'p1_2', '<b>2.0</b><br>Enrollment Fee Payment<br>&amp; Proof Verification', 420, 420, 230, 90, '#0284C7', '#0369A1')
    add_process(r1, 'p1_3', '<b>3.0</b><br>Budget Allocation &amp;<br>Fiscal Setup', 420, 680, 230, 90, '#0D9488', '#0F766E')
    add_process(r1, 'p1_4', '<b>4.0</b><br>Proposal, Fund Release,<br>Expenses &amp; Liquidation', 420, 980, 230, 90, '#7C3AED', '#6D28D9')
    add_process(r1, 'p1_5', '<b>5.0</b><br>Candidacy Filing &amp;<br>Secret Ballot Election', 420, 1280, 230, 90, '#D97706', '#B45309')
    add_process(r1, 'p1_6', '<b>6.0</b><br>Announcements, Community<br>Feedback &amp; Audit Logs', 420, 1550, 230, 90, '#E11D48', '#BE123C')

    add_datastore(r1, 'ds_d1', '<b>D1: eligible_students</b><br><font color="#64748B">{student_id, fullname, department, year_level, is_registered}</font>', 900, 160, 320, 60)
    add_datastore(r1, 'ds_d2', '<b>D2: users</b><br><font color="#64748B">{id, first_name, middle_name, last_name, fullname, email, password, role, department, student_id, status}</font>', 900, 240, 320, 60)
    add_datastore(r1, 'ds_d3', '<b>D3: enrollment_payments</b><br><font color="#64748B">{id, user_id, amount, semester, method, reference, status, proof_path, proof_status, proof_notes, paid_at}</font>', 900, 420, 320, 65)
    add_datastore(r1, 'ds_d4', '<b>D4: budgets</b><br><font color="#64748B">{id, title, department, allocated_amount, remaining_balance, school_year, status, created_by}</font>', 900, 670, 320, 65)
    add_datastore(r1, 'ds_d5', '<b>D5: proposals</b><br><font color="#64748B">{id, officer_id, project_title, requested_budget, approved_budget, description, status, project_status}</font>', 900, 890, 320, 65)
    add_datastore(r1, 'ds_d6', '<b>D6: budget_releases</b><br><font color="#64748B">{id, budget_id, proposal_id, released_by, amount, receipt, notes, released_at}</font>', 900, 970, 320, 60)
    add_datastore(r1, 'ds_d7', '<b>D7: expenses</b><br><font color="#64748B">{id, budget_id, officer_id, expense_title, amount, receipt, description, status}</font>', 900, 1045, 320, 60)
    add_datastore(r1, 'ds_d8', '<b>D8: liquidations</b><br><font color="#64748B">{id, proposal_id, officer_id, title, file_path, notes, status}</font>', 900, 1120, 320, 60)
    add_datastore(r1, 'ds_d9', '<b>D9: candidacies</b><br><font color="#64748B">{id, user_id, department, position, party, platform, photo_path, status, school_year}</font>', 900, 1260, 320, 60)
    add_datastore(r1, 'ds_d10', '<b>D10: student_ballots</b><br><font color="#64748B">{id, user_id, school_year, status}</font>', 900, 1335, 320, 55)
    add_datastore(r1, 'ds_d11', '<b>D11: votes</b><br><font color="#64748B">{id, user_id, candidacy_id, position, school_year, is_skip}</font>', 900, 1405, 320, 55)
    add_datastore(r1, 'ds_d12', '<b>D12: announcements</b><br><font color="#64748B">{id, title, content, image_path, category, created_by, project_id}</font>', 900, 1530, 320, 60)
    add_datastore(r1, 'ds_d13', '<b>D13: feedback</b><br><font color="#64748B">{id, student_id, message, status, reply, replied_by}</font>', 900, 1610, 320, 60)
    add_datastore(r1, 'ds_d14', '<b>D14: activity_logs</b><br><font color="#64748B">{id, user_id, action, details, ip_address, created_at}</font>', 900, 1690, 320, 55)

    add_flow(r1, 'fl1_1', 'l1_e_stud', 'p1_1', '{student_id, first_name, last_name, email, password, department}')
    add_flow(r1, 'fl1_2', 'p1_1', 'ds_d1', 'Check whitelist &amp; mark is_registered=true')
    add_flow(r1, 'fl1_3', 'p1_1', 'ds_d2', 'Insert user record {fullname, email, password_hash, role="student"}')
    add_flow(r1, 'fl1_4', 'ds_d2', 'p1_1', 'Authenticate credentials &amp; verify status')
    add_flow(r1, 'fl1_5', 'p1_1', 'l1_e_stud', 'Auth token / Session profile')

    add_flow(r1, 'fl2_1', 'l1_e_stud', 'p1_2', '{payment_method, proof_file, amount}')
    add_flow(r1, 'fl2_2', 'p1_2', 'ds_d3', 'Store payment {user_id, amount, method, reference, proof_path, proof_status="pending"}')
    add_flow(r1, 'fl2_3', 'l1_e_adm', 'p1_2', '{payment_id, proof_status, proof_notes}')
    add_flow(r1, 'fl2_4', 'p1_2', 'ds_d3', 'Update status="paid" or reject proof')
    add_flow(r1, 'fl2_5', 'ds_d3', 'p1_2', 'Fetch payment reference &amp; verification status')
    add_flow(r1, 'fl2_6', 'p1_2', 'l1_e_stud', 'Display payment status / receipt')

    add_flow(r1, 'fl3_1', 'l1_e_adm', 'p1_3', '{title, department, allocated_amount, school_year}')
    add_flow(r1, 'fl3_2', 'p1_3', 'ds_d4', 'Save budget allocation')
    add_flow(r1, 'fl3_3', 'ds_d4', 'p1_3', 'Retrieve balances &amp; allocations')
    add_flow(r1, 'fl3_4', 'p1_3', 'l1_e_treas', 'Department budget summaries')

    add_flow(r1, 'fl4_1', 'l1_e_off', 'p1_4', 'Submit proposal {project_title, requested_budget, description}')
    add_flow(r1, 'fl4_2', 'p1_4', 'ds_d5', 'Save proposal record')
    add_flow(r1, 'fl4_3', 'l1_e_adm', 'p1_4', 'Review proposal {approved_budget, status, admin_notes}')
    add_flow(r1, 'fl4_4', 'p1_4', 'ds_d5', 'Update proposal status &amp; approved amount')
    add_flow(r1, 'fl4_5', 'l1_e_treas', 'p1_4', 'Disburse funds {budget_id, proposal_id, amount, receipt}')
    add_flow(r1, 'fl4_6', 'p1_4', 'ds_d6', 'Record fund release')
    add_flow(r1, 'fl4_7', 'p1_4', 'ds_d4', 'Deduct remaining_balance')
    add_flow(r1, 'fl4_8', 'l1_e_off', 'p1_4', 'File expense {expense_title, amount, receipt} &amp; liquidation {file_path}')
    add_flow(r1, 'fl4_9', 'p1_4', 'ds_d7', 'Store expense records')
    add_flow(r1, 'fl4_10', 'p1_4', 'ds_d8', 'Store liquidation documents')

    add_flow(r1, 'fl5_1', 'l1_e_stud', 'p1_5', 'Candidacy application {position, platform, photo_file}')
    add_flow(r1, 'fl5_2', 'p1_5', 'ds_d9', 'Store candidate profile')
    add_flow(r1, 'fl5_3', 'l1_e_dean', 'p1_5', 'Dean candidacy endorsement {candidacy_id, vote}')
    add_flow(r1, 'fl5_4', 'l1_e_stud', 'p1_5', 'Cast ballot {candidacy_id, position, is_skip}')
    add_flow(r1, 'fl5_5', 'p1_5', 'ds_d10', 'Record ballot completion')
    add_flow(r1, 'fl5_6', 'p1_5', 'ds_d11', 'Store encrypted/anonymized votes')
    add_flow(r1, 'fl5_7', 'ds_d11', 'p1_5', 'Tabulate official results')
    add_flow(r1, 'fl5_8', 'p1_5', 'l1_e_stud', 'Certified election winners')

    add_flow(r1, 'fl6_1', 'l1_e_adm', 'p1_6', 'Publish announcement {title, content, category, image}')
    add_flow(r1, 'fl6_2', 'p1_6', 'ds_d12', 'Save announcement')
    add_flow(r1, 'fl6_3', 'ds_d12', 'p1_6', 'Fetch live announcement feed')
    add_flow(r1, 'fl6_4', 'p1_6', 'l1_e_stud', 'Display news &amp; lost items')
    add_flow(r1, 'fl6_5', 'l1_e_stud', 'p1_6', 'Submit feedback {message}')
    add_flow(r1, 'fl6_6', 'p1_6', 'ds_d13', 'Store feedback message')
    add_flow(r1, 'fl6_7', 'l1_e_adm', 'p1_6', 'Reply to feedback {feedback_id, reply_text}')
    add_flow(r1, 'fl6_8', 'p1_6', 'ds_d13', 'Update feedback status="Replied"')
    add_flow(r1, 'fl6_9', 'p1_6', 'ds_d14', 'Write audit trail {user_id, action, details, ip}')

    # =========================================================================
    # TAB 3: LEVEL 2/3 - Subsystem 1: User Management & Authentication
    # =========================================================================
    d2 = ET.SubElement(mxfile, 'diagram', {'id': 'dfd-level-2-auth', 'name': 'Level 2 - 1.0 Auth & Whitelist'})
    m2 = ET.SubElement(d2, 'mxGraphModel', {'dx': '2400', 'dy': '1600', 'grid': '1', 'gridSize': '10', 'page': '1', 'pageWidth': '2400', 'pageHeight': '1600'})
    r2 = ET.SubElement(m2, 'root')
    ET.SubElement(r2, 'mxCell', {'id': '0'})
    ET.SubElement(r2, 'mxCell', {'id': '1', 'parent': '0'})

    add_title(r2, 't2', 'DETAILED DATA FLOW: 1.0 USER IDENTITY, WHITELIST &amp; AUTHENTICATION', 'Level 2 &amp; 3 Sub-Process Decomposition: Whitelist Matching, Provisioning &amp; MFA Session Management', 600, 30, 1200, 60)

    add_entity(r2, 'e2_stud', 'STUDENT REGISTRANT', 60, 200, 200, 75, '#1E3A8A')
    add_entity(r2, 'e2_adm', 'SSC ADMIN / REGISTRAR', 60, 700, 200, 75, '#991B1B')

    add_process(r2, 'p2_1_1', '<b>1.1</b><br>Roster Whitelist Import<br>&amp; CSV Ingestion', 420, 700, 220, 85, '#2563EB')
    add_process(r2, 'p2_1_2', '<b>1.2</b><br>Student Identity Lookup<br>&amp; Eligibility Matching', 420, 200, 220, 85, '#2563EB')
    add_process(r2, 'p2_1_3', '<b>1.3</b><br>Account Provisioning &amp;<br>Bcrypt Hash Encryption', 780, 200, 220, 85, '#2563EB')
    add_process(r2, 'p2_1_4', '<b>1.4</b><br>Role-based Portal Auth<br>&amp; Captcha Verification', 1140, 200, 220, 85, '#2563EB')
    add_process(r2, 'p2_1_5', '<b>1.5</b><br>Admin MFA Approval /<br>Device Token Binding', 1140, 700, 220, 85, '#2563EB')

    add_datastore(r2, 'ds2_d1', '<b>D1: eligible_students</b><br><font color="#475569">• student_id (varchar PK)<br>• fullname, department, year_level<br>• is_registered (boolean)</font>', 420, 450, 240, 90)
    add_datastore(r2, 'ds2_d2', '<b>D2: users</b><br><font color="#475569">• id (bigint PK)<br>• first_name, middle_name, last_name<br>• age, year_level, fullname, email<br>• password (bcrypt), role, department<br>• student_id, status, remember_token</font>', 780, 450, 280, 130)
    add_datastore(r2, 'ds2_sess', '<b>D16: sessions / device_tokens</b><br><font color="#475569">• id, user_id, ip_address, payload<br>• token, platform, last_activity</font>', 1140, 450, 260, 90)

    add_flow(r2, 'f2_1_1', 'e2_adm', 'p2_1_1', 'Upload eligible roster CSV: {student_id, fullname, department, year_level}')
    add_flow(r2, 'f2_1_2', 'p2_1_1', 'ds2_d1', 'Bulk INSERT INTO eligible_students (is_registered=0)')
    add_flow(r2, 'f2_1_3', 'e2_stud', 'p2_1_2', 'Registration submission: {student_id, first_name, middle_name, last_name, email, password, department, year_level}')
    add_flow(r2, 'f2_1_4', 'p2_1_2', 'ds2_d1', 'SELECT * WHERE student_id=input.student_id AND is_registered=0')
    add_flow(r2, 'f2_1_5', 'ds2_d1', 'p2_1_2', 'Matched student record: {fullname, department, year_level}')
    add_flow(r2, 'f2_1_6', 'p2_1_2', 'p2_1_3', 'Validated student packet')
    add_flow(r2, 'f2_1_7', 'p2_1_3', 'ds2_d2', 'INSERT INTO users: {first_name, middle_name, last_name, fullname, email, password=bcrypt(pwd), role="student", department, student_id, status="active"}')
    add_flow(r2, 'f2_1_8', 'p2_1_3', 'ds2_d1', 'UPDATE eligible_students SET is_registered=1')
    add_flow(r2, 'f2_1_9', 'e2_stud', 'p2_1_4', 'Login Form: {email, password, captcha}')
    add_flow(r2, 'f2_1_10', 'p2_1_4', 'ds2_d2', 'SELECT * WHERE email=input.email')
    add_flow(r2, 'f2_1_11', 'p2_1_4', 'ds2_sess', 'Store auth session &amp; bind device token')
    add_flow(r2, 'f2_1_12', 'p2_1_4', 'e2_stud', 'Authenticated redirect to Student Overview')
    add_flow(r2, 'f2_1_13', 'e2_adm', 'p2_1_5', 'Admin Login -> Multi-Factor Challenge')
    add_flow(r2, 'f2_1_14', 'p2_1_5', 'ds2_sess', 'Verify trusted device token or send OTP fallback')

    # =========================================================================
    # TAB 4: LEVEL 2/3 - Subsystem 2: Enrollment Fee & Proof Verification
    # =========================================================================
    d3 = ET.SubElement(mxfile, 'diagram', {'id': 'dfd-level-2-enrollment', 'name': 'Level 2 - 2.0 Enrollment Payment'})
    m3 = ET.SubElement(d3, 'mxGraphModel', {'dx': '2400', 'dy': '1600', 'grid': '1', 'gridSize': '10', 'page': '1', 'pageWidth': '2400', 'pageHeight': '1600'})
    r3 = ET.SubElement(m3, 'root')
    ET.SubElement(r3, 'mxCell', {'id': '0'})
    ET.SubElement(r3, 'mxCell', {'id': '1', 'parent': '0'})

    add_title(r3, 't3', 'DETAILED DATA FLOW: 2.0 ENROLLMENT FEE PAYMENT &amp; PROOF VERIFICATION', 'Level 2 &amp; 3 Sub-Process Decomposition: Method Selection, Cloudinary Proof Storage &amp; Admin Audit Verification', 600, 30, 1200, 60)

    add_entity(r3, 'e3_stud', 'STUDENT PAYER', 60, 220, 200, 75, '#1E3A8A')
    add_entity(r3, 'e3_adm', 'SSC ADMIN / TREASURER', 2100, 220, 220, 75, '#991B1B')
    add_entity(r3, 'e3_cloud', 'CLOUDINARY STORAGE<br><span style="font-size:10px;font-weight:normal;color:#93C5FD;">(Cloud Media Store)</span>', 800, 800, 220, 70, '#0369A1')

    add_process(r3, 'p3_2_1', '<b>2.1</b><br>Active Term &amp; Payment<br>Method Instructions', 380, 220, 220, 85, '#0284C7')
    add_process(r3, 'p3_2_2', '<b>2.2</b><br>Proof File Validation<br>&amp; Strict Requirement Check', 720, 220, 220, 85, '#0284C7')
    add_process(r3, 'p3_2_3', '<b>2.3</b><br>Cloudinary Asset Upload<br>&amp; Local Public Fallback', 1060, 220, 220, 85, '#0284C7')
    add_process(r3, 'p3_2_4', '<b>2.4</b><br>Reference Generation &amp;<br>Payment Record Storage', 1400, 220, 220, 85, '#0284C7')
    add_process(r3, 'p3_2_5', '<b>2.5</b><br>Admin Verification Queue<br>&amp; Approval Decision', 1740, 220, 220, 85, '#0284C7')

    add_datastore(r3, 'ds3_sy', '<b>D15: school_years</b><br><font color="#475569">• id, label (e.g. "2026-2027")<br>• is_active (boolean)</font>', 380, 480, 220, 75)
    add_datastore(r3, 'ds3_d3', '<b>D3: enrollment_payments</b><br><font color="#475569">• id (bigint PK)<br>• user_id (FK to users)<br>• amount (decimal: 50.00)<br>• semester ("2026-2027")<br>• method ("gcash"|"instapay"|"walk_in")<br>• reference ("GCASH-66CF..." | "INSTAPAY-...")<br>• status ("pending" | "paid")<br>• proof_path (URL / storage path)<br>• proof_status ("pending"|"approved"|"rejected")<br>• proof_notes, verified_by, paid_at</font>', 1060, 480, 320, 210)

    add_flow(r3, 'f3_2_1', 'e3_stud', 'p3_2_1', 'Request Enrollment Payment Page')
    add_flow(r3, 'f3_2_2', 'ds3_sy', 'p3_2_1', 'Fetch active school year label')
    add_flow(r3, 'f3_2_3', 'p3_2_1', 'e3_stud', 'Render fee amount (P50), GCash/InstaPay details &amp; dynamic instructions')
    add_flow(r3, 'f3_2_4', 'e3_stud', 'p3_2_2', '<b>Payment Submit Form:</b><br>{payment_method, proof: file (required), user_id}')
    add_flow(r3, 'f3_2_5', 'p3_2_2', 'p3_2_3', 'Validated file binary (JPG, PNG, PDF, MP4 &lt;= 5MB)')
    add_flow(r3, 'f3_2_6', 'p3_2_3', 'e3_cloud', 'POST SscHelper::uploadToCloudinary(file, "enrollment_proofs")')
    add_flow(r3, 'f3_2_7', 'e3_cloud', 'p3_2_3', 'Cloudinary secure URL: https://res.cloudinary.com/.../proof.png')
    add_flow(r3, 'f3_2_8', 'p3_2_3', 'p3_2_4', 'proof_path (Cloudinary URL or local disk storage path)')
    add_flow(r3, 'f3_2_9', 'p3_2_4', 'ds3_d3', '<b>INSERT INTO enrollment_payments:</b><br>{user_id, amount=50.00, semester=active_sy, method=method, status="pending", reference=prefix+uniqid(), proof_path=url, proof_status="pending"}')
    add_flow(r3, 'f3_2_10', 'p3_2_4', 'e3_stud', 'Flash success: "Payment proof uploaded. Admin will verify shortly." + Reference Code')
    add_flow(r3, 'f3_2_11', 'ds3_d3', 'p3_2_5', 'Pending verification queue record')
    add_flow(r3, 'f3_2_12', 'e3_adm', 'p3_2_5', '<b>Admin Decision:</b><br>{payment_id, action: "approve"|"reject", proof_notes}')
    add_flow(r3, 'f3_2_13', 'p3_2_5', 'ds3_d3', '<b>UPDATE enrollment_payments:</b><br>SET proof_status="approved", status="paid", verified_by=admin_id, paid_at=NOW()')
    add_flow(r3, 'f3_2_14', 'p3_2_5', 'e3_stud', 'Updated clearance status: "You have already paid for this semester."')

    # =========================================================================
    # TAB 5: LEVEL 2/3 - Subsystem 3: Budget Allocation & Fiscal Management
    # =========================================================================
    d4 = ET.SubElement(mxfile, 'diagram', {'id': 'dfd-level-2-budget', 'name': 'Level 2 - 3.0 Budget Allocation'})
    m4 = ET.SubElement(d4, 'mxGraphModel', {'dx': '2400', 'dy': '1600', 'grid': '1', 'gridSize': '10', 'page': '1', 'pageWidth': '2400', 'pageHeight': '1600'})
    r4 = ET.SubElement(m4, 'root')
    ET.SubElement(r4, 'mxCell', {'id': '0'})
    ET.SubElement(r4, 'mxCell', {'id': '1', 'parent': '0'})

    add_title(r4, 't4', 'DETAILED DATA FLOW: 3.0 BUDGET ALLOCATION &amp; FISCAL SETUP', 'Level 2 &amp; 3 Sub-Process Decomposition: Department Apportionment, Fiscal Setup &amp; Balance Monitoring', 600, 30, 1200, 60)

    add_entity(r4, 'e4_adm', 'SSC ADMIN / ADVISER', 60, 240, 200, 75, '#991B1B')
    add_entity(r4, 'e4_treas', 'SSC TREASURER', 2100, 240, 200, 75, '#065F46')

    add_process(r4, 'p4_3_1', '<b>3.1</b><br>Fiscal Year Activation<br>&amp; Parameter Setup', 400, 240, 220, 85, '#0D9488')
    add_process(r4, 'p4_3_2', '<b>3.2</b><br>Department Budget Setup<br>&amp; Fund Allocation', 800, 240, 220, 85, '#0D9488')
    add_process(r4, 'p4_3_3', '<b>3.3</b><br>Budget Approval &amp;<br>Initial Balance Provisioning', 1200, 240, 220, 85, '#0D9488')
    add_process(r4, 'p4_3_4', '<b>3.4</b><br>Real-time Balance Audit<br>&amp; Financial Ledger Export', 1600, 240, 220, 85, '#0D9488')

    add_datastore(r4, 'ds4_sy', '<b>D15: school_years</b><br><font color="#475569">• id, label, is_active</font>', 400, 500, 220, 75)
    add_datastore(r4, 'ds4_d4', '<b>D4: budgets</b><br><font color="#475569">• id (bigint PK)<br>• title, department<br>• allocated_amount (decimal 15,2)<br>• remaining_balance (decimal 15,2)<br>• school_year, status ("Pending"|"Approved"|"Rejected")<br>• created_by, approved_by, notes</font>', 1000, 500, 300, 140)

    add_flow(r4, 'f4_3_1', 'e4_adm', 'p4_3_1', 'Create/Activate School Year: {label: "2026-2027", is_active=1}')
    add_flow(r4, 'f4_3_2', 'p4_3_1', 'ds4_sy', 'INSERT/UPDATE school_years')
    add_flow(r4, 'f4_3_3', 'e4_adm', 'p4_3_2', 'New Budget Form: {title, department, allocated_amount, school_year, notes}')
    add_flow(r4, 'f4_3_4', 'p4_3_2', 'p4_3_3', 'Validated budget allocation packet')
    add_flow(r4, 'f4_3_5', 'p4_3_3', 'ds4_d4', '<b>INSERT INTO budgets:</b><br>{title, department, allocated_amount, remaining_balance=allocated_amount, school_year, status="Approved", created_by=admin_id}')
    add_flow(r4, 'f4_3_6', 'ds4_d4', 'p4_3_4', 'Active budget balances and allocation lines')
    add_flow(r4, 'f4_3_7', 'p4_3_4', 'e4_treas', 'Department budget summaries &amp; disbursement ledger')
    add_flow(r4, 'f4_3_8', 'p4_3_4', 'e4_adm', 'Consolidated financial reports &amp; Excel/PDF export')

    # =========================================================================
    # TAB 6: LEVEL 2/3 - Subsystem 4: Proposals, Release, Expenses & Liquidation
    # =========================================================================
    d5 = ET.SubElement(mxfile, 'diagram', {'id': 'dfd-level-2-proposals', 'name': 'Level 2 - 4.0 Proposals & Finance'})
    m5 = ET.SubElement(d5, 'mxGraphModel', {'dx': '2400', 'dy': '1600', 'grid': '1', 'gridSize': '10', 'page': '1', 'pageWidth': '2400', 'pageHeight': '1600'})
    r5 = ET.SubElement(m5, 'root')
    ET.SubElement(r5, 'mxCell', {'id': '0'})
    ET.SubElement(r5, 'mxCell', {'id': '1', 'parent': '0'})

    add_title(r5, 't5', 'DETAILED DATA FLOW: 4.0 PROJECT PROPOSALS, FUND DISBURSEMENT &amp; LIQUIDATION', 'Level 2 &amp; 3 Sub-Process Decomposition: Project Drafting, Review, Fund Release, Expense Auditing &amp; Liquidation', 600, 30, 1200, 60)

    add_entity(r5, 'e5_off', 'SSC OFFICER (PROPONENT)', 60, 220, 220, 75, '#5B21B6')
    add_entity(r5, 'e5_adm', 'SSC ADMIN / ADVISER', 1100, 80, 220, 75, '#991B1B')
    add_entity(r5, 'e5_treas', 'SSC TREASURER', 2100, 220, 220, 75, '#065F46')

    add_process(r5, 'p5_4_1', '<b>4.1</b><br>Proposal Submission &amp;<br>Budget Request Drafting', 420, 220, 220, 85, '#7C3AED')
    add_process(r5, 'p5_4_2', '<b>4.2</b><br>Admin Proposal Review<br>&amp; Grant Allocation', 800, 220, 220, 85, '#7C3AED')
    add_process(r5, 'p5_4_3', '<b>4.3</b><br>Treasurer Fund Release<br>&amp; Balance Deduction', 1400, 220, 220, 85, '#059669')
    add_process(r5, 'p5_4_4', '<b>4.4</b><br>Expense Receipts &amp;<br>Disbursement Tracking', 420, 750, 220, 85, '#059669')
    add_process(r5, 'p5_4_5', '<b>4.5</b><br>Liquidation Filing &amp;<br>Project Completion Audit', 1000, 750, 220, 85, '#059669')

    add_datastore(r5, 'ds5_d5', '<b>D5: proposals</b><br><font color="#475569">• id (bigint PK), officer_id<br>• project_title, requested_budget<br>• approved_budget, description<br>• status ("Pending"|"Approved"|"Rejected")<br>• project_status ("Pending"|"Ongoing"|"Completed")<br>• completion_proof, approved_by, admin_notes</font>', 800, 450, 280, 150)
    add_datastore(r5, 'ds5_d6', '<b>D6: budget_releases</b><br><font color="#475569">• id, budget_id, proposal_id<br>• released_by, amount, receipt<br>• notes, released_at</font>', 1400, 450, 240, 90)
    add_datastore(r5, 'ds5_d4', '<b>D4: budgets</b><br><font color="#475569">• id, title, department<br>• allocated_amount, remaining_balance</font>', 1750, 450, 240, 85)
    add_datastore(r5, 'ds5_d7', '<b>D7: expenses</b><br><font color="#475569">• id, budget_id, officer_id<br>• expense_title, amount, receipt<br>• description, status, admin_notes</font>', 420, 1000, 260, 95)
    add_datastore(r5, 'ds5_d8', '<b>D8: liquidations</b><br><font color="#475569">• id, proposal_id, officer_id<br>• title, file_path, notes, status</font>', 1000, 1000, 260, 90)

    add_flow(r5, 'f5_4_1', 'e5_off', 'p5_4_1', 'Submit Proposal Form: {project_title, requested_budget, description}')
    add_flow(r5, 'f5_4_2', 'p5_4_1', 'ds5_d5', 'INSERT INTO proposals (status="Pending")')
    add_flow(r5, 'f5_4_3', 'ds5_d5', 'p5_4_2', 'Fetch pending proposal for review')
    add_flow(r5, 'f5_4_4', 'e5_adm', 'p5_4_2', 'Review Action: {approved_budget, decision: "Approved"|"Rejected", admin_notes}')
    add_flow(r5, 'f5_4_5', 'p5_4_2', 'ds5_d5', 'UPDATE proposals SET approved_budget=..., status="Approved"')
    add_flow(r5, 'f5_4_6', 'ds5_d5', 'p5_4_3', 'Approved proposal pending release')
    add_flow(r5, 'f5_4_7', 'e5_treas', 'p5_4_3', 'Fund Release Form: {budget_id, proposal_id, amount, receipt_doc, notes}')
    add_flow(r5, 'f5_4_8', 'p5_4_3', 'ds5_d6', 'INSERT INTO budget_releases: {budget_id, proposal_id, released_by, amount, receipt, notes, released_at=NOW()}')
    add_flow(r5, 'f5_4_9', 'p5_4_3', 'ds5_d4', 'UPDATE budgets SET remaining_balance = remaining_balance - amount')
    add_flow(r5, 'f5_4_10', 'e5_off', 'p5_4_4', 'Log Expense Claim: {budget_id, expense_title, amount, receipt_file, description}')
    add_flow(r5, 'f5_4_11', 'p5_4_4', 'ds5_d7', 'INSERT INTO expenses: {budget_id, officer_id, expense_title, amount, receipt, status="Pending"}')
    add_flow(r5, 'f5_4_12', 'e5_off', 'p5_4_5', 'Submit Liquidation &amp; Completion: {proposal_id, title, liquidation_pdf, completion_proof_photo}')
    add_flow(r5, 'f5_4_13', 'p5_4_5', 'ds5_d8', 'INSERT INTO liquidations: {proposal_id, officer_id, title, file_path, status="Pending"}')
    add_flow(r5, 'f5_4_14', 'p5_4_5', 'ds5_d5', 'UPDATE proposals SET project_status="Completed", completion_proof=url')

    # =========================================================================
    # TAB 7: LEVEL 2/3 - Subsystem 5: Candidacy, Voting & Election System
    # =========================================================================
    d6 = ET.SubElement(mxfile, 'diagram', {'id': 'dfd-level-2-election', 'name': 'Level 2 - 5.0 Candidacy & Election'})
    m6 = ET.SubElement(d6, 'mxGraphModel', {'dx': '2400', 'dy': '1600', 'grid': '1', 'gridSize': '10', 'page': '1', 'pageWidth': '2400', 'pageHeight': '1600'})
    r6 = ET.SubElement(m6, 'root')
    ET.SubElement(r6, 'mxCell', {'id': '0'})
    ET.SubElement(r6, 'mxCell', {'id': '1', 'parent': '0'})

    add_title(r6, 't6', 'DETAILED DATA FLOW: 5.0 CANDIDACY FILING &amp; SECRET BALLOT ELECTION', 'Level 2 &amp; 3 Sub-Process Decomposition: Candidacy Endorsement, Secret Ballot &amp; Automated Tallying', 600, 30, 1200, 60)

    add_entity(r6, 'e6_stud', 'STUDENT VOTER / CANDIDATE', 60, 220, 220, 75, '#1E3A8A')
    add_entity(r6, 'e6_dean', 'COLLEGE DEAN', 60, 750, 220, 75, '#92400E')
    add_entity(r6, 'e6_adm', 'SSC ADMIN / ELECOM', 2100, 220, 220, 75, '#991B1B')

    add_process(r6, 'p6_5_1', '<b>5.1</b><br>Candidacy Application &amp;<br>Department Restriction Check', 420, 220, 220, 85, '#D97706')
    add_process(r6, 'p6_5_2', '<b>5.2</b><br>Dean Electoral Evaluation<br>&amp; Endorsement Voting', 820, 220, 220, 85, '#D97706')
    add_process(r6, 'p6_5_3', '<b>5.3</b><br>Ballot Initialization &amp;<br>Anti-Double-Vote Guard', 1220, 220, 220, 85, '#D97706')
    add_process(r6, 'p6_5_4', '<b>5.4</b><br>Secret Ballot Voting &amp;<br>Position Selection / Skip', 1620, 220, 220, 85, '#D97706')
    add_process(r6, 'p6_5_5', '<b>5.5</b><br>Automated Vote Tallying<br>&amp; Certified Proclamation', 1220, 750, 220, 85, '#D97706')

    add_datastore(r6, 'ds6_sy', '<b>D15: school_years</b><br><font color="#475569">• id, label, candidacy_open<br>• voting_open, results_announced</font>', 420, 480, 220, 85)
    add_datastore(r6, 'ds6_d9', '<b>D9: candidacies</b><br><font color="#475569">• id, user_id, department<br>• position, party, platform, photo_path<br>• status ("pending"|"approved"|"rejected")<br>• school_year, review_notes</font>', 820, 480, 260, 110)
    add_datastore(r6, 'ds6_d10', '<b>D10: student_ballots</b><br><font color="#475569">• id, user_id, school_year<br>• status ("in_progress"|"completed")<br>• created_at, completed_at</font>', 1220, 480, 240, 90)
    add_datastore(r6, 'ds6_d11', '<b>D11: votes</b><br><font color="#475569">• id, user_id, candidacy_id<br>• position, school_year, is_skip<br>• created_at</font>', 1620, 480, 240, 90)

    add_flow(r6, 'f6_5_1', 'e6_stud', 'p6_5_1', 'Submit Candidacy: {position, platform, photo_file, department}')
    add_flow(r6, 'f6_5_2', 'ds6_sy', 'p6_5_1', 'Verify candidacy_open == true')
    add_flow(r6, 'f6_5_3', 'p6_5_1', 'ds6_d9', 'INSERT INTO candidacies (status="pending")')
    add_flow(r6, 'f6_5_4', 'ds6_d9', 'p6_5_2', 'Pending candidate list by department')
    add_flow(r6, 'f6_5_5', 'e6_dean', 'p6_5_2', 'Dean Endorsement: {candidacy_id, vote: "approve"|"reject"}')
    add_flow(r6, 'f6_5_6', 'p6_5_2', 'ds6_d9', 'UPDATE candidacies SET status="approved"')
    add_flow(r6, 'f6_5_7', 'e6_stud', 'p6_5_3', 'Open Voting Session: {user_id}')
    add_flow(r6, 'f6_5_8', 'ds6_sy', 'p6_5_3', 'Verify voting_open == true')
    add_flow(r6, 'f6_5_9', 'p6_5_3', 'ds6_d10', 'INSERT INTO student_ballots (status="in_progress")')
    add_flow(r6, 'f6_5_10', 'ds6_d9', 'p6_5_3', 'Fetch approved candidates for student\'s department')
    add_flow(r6, 'f6_5_11', 'p6_5_3', 'e6_stud', 'Interactive digital ballot paper')
    add_flow(r6, 'f6_5_12', 'e6_stud', 'p6_5_4', 'Cast Votes: {position, candidacy_id OR is_skip=1}')
    add_flow(r6, 'f6_5_13', 'p6_5_4', 'ds6_d11', 'INSERT INTO votes {user_id, candidacy_id, position, school_year, is_skip}')
    add_flow(r6, 'f6_5_14', 'p6_5_4', 'ds6_d10', 'UPDATE student_ballots SET status="completed"')
    add_flow(r6, 'f6_5_15', 'e6_adm', 'p6_5_5', 'Elecom Trigger: Close voting &amp; announce results')
    add_flow(r6, 'f6_5_16', 'ds6_d11', 'p6_5_5', 'COUNT(votes) GROUP BY candidacy_id, position')
    add_flow(r6, 'f6_5_17', 'p6_5_5', 'e6_stud', 'Official Certified Election Results &amp; Winner Proclamation')

    # =========================================================================
    # TAB 8: LEVEL 2/3 - Subsystem 6: Announcements, Feedback & Audit Logs
    # =========================================================================
    d7 = ET.SubElement(mxfile, 'diagram', {'id': 'dfd-level-2-announcements', 'name': 'Level 2 - 6.0 Announcements & Feedback'})
    m7 = ET.SubElement(d7, 'mxGraphModel', {'dx': '2400', 'dy': '1600', 'grid': '1', 'gridSize': '10', 'page': '1', 'pageWidth': '2400', 'pageHeight': '1600'})
    r7 = ET.SubElement(m7, 'root')
    ET.SubElement(r7, 'mxCell', {'id': '0'})
    ET.SubElement(r7, 'mxCell', {'id': '1', 'parent': '0'})

    add_title(r7, 't7', 'DETAILED DATA FLOW: 6.0 ANNOUNCEMENTS, COMMUNITY FEEDBACK &amp; AUDIT LOGS', 'Level 2 &amp; 3 Sub-Process Decomposition: Media Broadcasting, Lost &amp; Found, Feedback Resolution &amp; Activity Auditing', 600, 30, 1200, 60)

    add_entity(r7, 'e7_stud', 'STUDENT COMMUNITY', 60, 220, 220, 75, '#1E3A8A')
    add_entity(r7, 'e7_adm', 'SSC ADMIN / OFFICERS', 2100, 220, 220, 75, '#991B1B')

    add_process(r7, 'p7_6_1', '<b>6.1</b><br>Announcement Publishing<br>&amp; Category Routing', 420, 220, 220, 85, '#E11D48')
    add_process(r7, 'p7_6_2', '<b>6.2</b><br>Lost &amp; Found Item Feed<br>&amp; Student Commenting', 840, 220, 220, 85, '#E11D48')
    add_process(r7, 'p7_6_3', '<b>6.3</b><br>Student Grievance &amp;<br>Feedback Ingestion', 1260, 220, 220, 85, '#E11D48')
    add_process(r7, 'p7_6_4', '<b>6.4</b><br>Admin Response &amp;<br>Two-Way Resolution', 1680, 220, 220, 85, '#E11D48')
    add_process(r7, 'p7_6_5', '<b>6.5</b><br>Push Notification Broadcast<br>&amp; Activity Audit Logging', 1050, 750, 240, 85, '#E11D48')

    add_datastore(r7, 'ds7_d12', '<b>D12: announcements</b><br><font color="#475569">• id, title, content, image_path<br>• category ("general"|"news"|"lost_item"|"event")<br>• created_by, project_id</font>', 420, 480, 260, 95)
    add_datastore(r7, 'ds7_d12c', '<b>D12.1: announcement_comments</b><br><font color="#475569">• id, announcement_id, user_id, comment</font>', 840, 480, 260, 75)
    add_datastore(r7, 'ds7_d13', '<b>D13: feedback</b><br><font color="#475569">• id, student_id, message<br>• status ("Pending"|"Replied")<br>• reply, replied_by, created_at</font>', 1470, 480, 260, 95)
    add_datastore(r7, 'ds7_d14', '<b>D14: activity_logs</b><br><font color="#475569">• id, user_id, action, details<br>• ip_address, created_at</font>', 1050, 980, 240, 85)

    add_flow(r7, 'f7_6_1', 'e7_adm', 'p7_6_1', 'Publish Announcement: {title, content, category, image_file, project_id}')
    add_flow(r7, 'f7_6_2', 'p7_6_1', 'ds7_d12', 'INSERT INTO announcements')
    add_flow(r7, 'f7_6_3', 'ds7_d12', 'p7_6_2', 'Fetch live feed (News, Lost &amp; Found, Projects)')
    add_flow(r7, 'f7_6_4', 'p7_6_2', 'e7_stud', 'Display announcements &amp; completion showcases')
    add_flow(r7, 'f7_6_5', 'e7_stud', 'p7_6_2', 'Comment on Lost Item: {announcement_id, comment_text}')
    add_flow(r7, 'f7_6_6', 'p7_6_2', 'ds7_d12c', 'INSERT INTO announcement_comments')
    add_flow(r7, 'f7_6_7', 'e7_stud', 'p7_6_3', 'Submit Feedback / Inquiry: {message, student_id}')
    add_flow(r7, 'f7_6_8', 'p7_6_3', 'ds7_d13', 'INSERT INTO feedback (status="Pending")')
    add_flow(r7, 'f7_6_9', 'ds7_d13', 'p7_6_4', 'Pending feedback items in Admin inbox')
    add_flow(r7, 'f7_6_10', 'e7_adm', 'p7_6_4', 'Submit Admin Reply: {feedback_id, reply_text}')
    add_flow(r7, 'f7_6_11', 'p7_6_4', 'ds7_d13', 'UPDATE feedback SET status="Replied", reply=..., replied_by=admin_id')
    add_flow(r7, 'f7_6_12', 'p7_6_4', 'e7_stud', 'Notify student: Admin reply visible in Student Feedback portal')
    add_flow(r7, 'f7_6_13', 'p7_6_1', 'p7_6_5', 'Trigger push notification &amp; audit event')
    add_flow(r7, 'f7_6_14', 'p7_6_5', 'ds7_d14', 'INSERT INTO activity_logs: {user_id, action, details, ip_address, created_at=NOW()}')

    return mxfile

if __name__ == '__main__':
    root_xml = generate_complete_dfd_all_levels()
    tree = ET.ElementTree(root_xml)
    ET.indent(tree, space="  ", level=0)
    output_path = os.path.abspath('docs/SSC_DFD.drawio')
    os.makedirs(os.path.dirname(output_path), exist_ok=True)
    tree.write(output_path, encoding='utf-8', xml_declaration=False)
    print(f"Successfully generated all-levels DFD at: {output_path}")
