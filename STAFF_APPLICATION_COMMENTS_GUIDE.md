# Staff Application Comments & Feedback System

## 📋 Overview

The staff application system now has a comprehensive comment and feedback system that allows gym owners to provide detailed feedback on each document (Medical Certificate and Resume/CV).

## 🎯 Features

### For Gym Owners (Reviewers):

1. **Per-Document Comments**
   - Add specific comments for each document
   - Mark documents as Approved, Flagged, or Pending
   - Check "Verified/Reviewed" checkbox to track review progress

2. **Overall Feedback**
   - Provide general feedback for the entire application
   - Feedback is visible to the applicant

3. **Document Status Options**
   - ✅ **Approve** - Document is acceptable
   - 🚩 **Flag Issue** - Document needs correction (with comment explaining why)
   - 🔄 **Reset** - Return to pending status

### For Applicants (Staff):

1. **View Document Status**
   - See status for each document (Pending/Approved/Flagged)
   - Read comments from gym owner
   - View overall feedback

2. **Resubmit Flagged Documents**
   - Resubmit individual flagged documents
   - Or resubmit all documents at once

## 🔄 How It Works

### Gym Owner Review Process:

```
1. Go to "Staff Applications"
   ↓
2. Click "Review" on an application
   ↓
3. For each document:
   ├─ View the document (PDF/Image)
   ├─ Add comment (optional)
   ├─ Check "Verified/Reviewed" box
   └─ Click: Approve / Flag Issue / Reset
   ↓
4. Add overall feedback (optional)
   ↓
5. Final action:
   ├─ "Approve & Hire" - Hire the applicant
   └─ "Reject" - Reject the application
```

### Applicant View Process:

```
1. Submit application
   ↓
2. View application status page
   ↓
3. See document status cards:
   ├─ Pending (yellow) - Awaiting review
   ├─ Approved (green) - Document accepted
   └─ Flagged (red) - Needs correction
   ↓
4. If flagged:
   ├─ Read gym owner's comment
   ├─ Upload corrected document
   └─ Click "Resubmit [Document Name]"
   ↓
5. Document status resets to "Pending"
   ↓
6. Gym owner reviews again
```

## 📊 Database Structure

### staff_applications Table:

```sql
-- Per-document status and comments
medical_certificate_status ENUM('pending','approved','flagged')
medical_certificate_comment TEXT
medical_certificate_checked TINYINT(1)

resume_status ENUM('pending','approved','flagged')
resume_comment TEXT
resume_checked TINYINT(1)

-- Overall application
status ENUM('pending','approved','rejected','resubmit')
feedback TEXT
reviewer_id INT
```

## 🎨 User Interface

### Gym Owner Review Page:

```
┌─────────────────────────────────────────────────────────┐
│  Review Application #6                                  │
│  Applicant: John Doe (john@email.com)                  │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  ┌─────────────────────┐  ┌─────────────────────┐     │
│  │ Medical Certificate │  │ Resume / CV         │     │
│  │ Status: Pending     │  │ Status: Pending     │     │
│  │                     │  │                     │     │
│  │ [View Document]     │  │ [View Document]     │     │
│  │                     │  │                     │     │
│  │ ☐ Verified/Reviewed │  │ ☐ Verified/Reviewed │     │
│  │                     │  │                     │     │
│  │ Comment:            │  │ Comment:            │     │
│  │ [Text area]         │  │ [Text area]         │     │
│  │                     │  │                     │     │
│  │ [Approve] [Flag]    │  │ [Approve] [Flag]    │     │
│  │ [Reset]             │  │ [Reset]             │     │
│  └─────────────────────┘  └─────────────────────┘     │
│                                                         │
│  Overall Feedback:                                      │
│  [Text area for general feedback]                      │
│                                                         │
│  [Approve & Hire]  [Reject]                            │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

### Applicant View (When Flagged):

```
┌─────────────────────────────────────────────────────────┐
│  Apply as Staff                          [Refresh]      │
│  Submit your application to join Power Fitness          │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  ⚠️ Some documents need attention.                      │
│  Please review the feedback below and resubmit.         │
│                                                         │
│  ┌─────────────────────┐  ┌─────────────────────┐     │
│  │ ❌ Medical Cert     │  │ ✅ Resume / CV      │     │
│  │ Status: Flagged     │  │ Status: Approved    │     │
│  │                     │  │                     │     │
│  │ [View Current Doc]  │  │ [View Current Doc]  │     │
│  │                     │  │                     │     │
│  │ ⚠️ Issue:           │  │ ✅ This document    │     │
│  │ "Document expired"  │  │ has been approved.  │     │
│  │                     │  │ No action needed.   │     │
│  │ Upload corrected:   │  │                     │     │
│  │ [Choose File]       │  │                     │     │
│  │                     │  │                     │     │
│  │ [Resubmit Medical   │  │                     │     │
│  │  Certificate]       │  │                     │     │
│  └─────────────────────┘  └─────────────────────┘     │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

## 💡 Example Scenarios

### Scenario 1: Medical Certificate Expired

**Gym Owner:**
1. Views medical certificate
2. Notices it's expired
3. Adds comment: "Medical certificate expired. Please upload a current one dated within the last 6 months."
4. Clicks "Flag Issue"

**Applicant:**
1. Sees Medical Certificate status: "Flagged" (red)
2. Reads comment about expiration
3. Uploads new medical certificate
4. Clicks "Resubmit Medical Certificate"
5. Status changes to "Pending"

**Gym Owner:**
6. Reviews new medical certificate
7. Adds comment: "Looks good!"
8. Clicks "Approve"

### Scenario 2: Resume Missing Information

**Gym Owner:**
1. Views resume
2. Notices missing contact information
3. Adds comment: "Please include your phone number and complete address in your resume."
4. Clicks "Flag Issue"

**Applicant:**
1. Sees Resume status: "Flagged" (red)
2. Reads comment about missing info
3. Updates resume with contact information
4. Uploads corrected resume
5. Clicks "Resubmit Resume / CV"

**Gym Owner:**
6. Reviews updated resume
7. Clicks "Approve"
8. Both documents now approved
9. Clicks "Approve & Hire"

### Scenario 3: All Documents Good

**Gym Owner:**
1. Reviews medical certificate
2. Adds comment: "Valid and current"
3. Checks "Verified/Reviewed"
4. Clicks "Approve"
5. Reviews resume
6. Adds comment: "Excellent qualifications"
7. Checks "Verified/Reviewed"
8. Clicks "Approve"
9. Adds overall feedback: "Great candidate! Welcome to the team."
10. Clicks "Approve & Hire"

**Applicant:**
1. Sees both documents: "Approved" (green)
2. Sees overall feedback
3. Receives notification: "Application approved!"
4. Role changes to "trainer" or "maintenance"

## 🧪 Testing

### Test 1: Add Comment to Document

1. **Login as Gym Owner**
2. Go to "Staff Applications"
3. Click "Review" on an application
4. For Medical Certificate:
   - Add comment: "Please provide a clearer scan"
   - Click "Flag Issue"
5. ✅ **Expected:** Comment is saved
6. **Login as Applicant**
7. Go to staff application page
8. ✅ **Expected:** See comment "Please provide a clearer scan"

### Test 2: Resubmit Flagged Document

1. **As Applicant** (from Test 1)
2. Upload new medical certificate
3. Click "Resubmit Medical Certificate"
4. ✅ **Expected:** Success message appears
5. ✅ **Expected:** Status changes to "Pending"
6. ✅ **Expected:** Comment is cleared
7. **As Gym Owner**
8. Review the application again
9. ✅ **Expected:** New document is visible
10. ✅ **Expected:** Status is "Pending"

### Test 3: Overall Feedback

1. **As Gym Owner**
2. Review application
3. Approve both documents
4. Add overall feedback: "Welcome to the team!"
5. Click "Approve & Hire"
6. ✅ **Expected:** Application approved
7. **As Applicant**
8. View application page
9. ✅ **Expected:** See "Application Approved!"
10. ✅ **Expected:** See feedback "Welcome to the team!"

## 📝 Database Queries

### Check Document Comments:

```sql
SELECT 
    id,
    medical_certificate_status,
    medical_certificate_comment,
    resume_status,
    resume_comment,
    status,
    feedback
FROM staff_applications
WHERE id = [application_id];
```

### Check All Flagged Documents:

```sql
SELECT 
    sa.id,
    u.fullname as applicant_name,
    sa.medical_certificate_status,
    sa.medical_certificate_comment,
    sa.resume_status,
    sa.resume_comment
FROM staff_applications sa
JOIN users u ON u.id = sa.user_id
WHERE sa.medical_certificate_status = 'flagged' 
   OR sa.resume_status = 'flagged';
```

## ✅ Features Summary

- ✅ Per-document comments
- ✅ Per-document status (pending/approved/flagged)
- ✅ Overall application feedback
- ✅ Resubmit individual documents
- ✅ Resubmit all documents
- ✅ Auto-refresh to see updates
- ✅ Visual status indicators (colors, icons)
- ✅ Verified/Reviewed checkbox
- ✅ Comment history preserved until resubmission

## 🎉 Benefits

1. **Clear Communication** - Gym owners can explain exactly what needs to be fixed
2. **Efficient Process** - Applicants can resubmit only the flagged documents
3. **Transparency** - Both parties see the same information
4. **Audit Trail** - Comments and status changes are tracked
5. **Better Experience** - No confusion about what needs to be corrected

---

**Status:** ✅ Fully Implemented  
**Date:** May 3, 2026  
**Tested:** ✅ Yes
