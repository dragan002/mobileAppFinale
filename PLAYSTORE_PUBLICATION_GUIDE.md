# AtomicMe Google Play Store Publication Guide

**Status:** 🔴 Not Started
**Last Updated:** 2026-03-29
**Target Launch Date:** TBD

---

## 📋 Quick Overview

This is your working checklist to publish **AtomicMe** to Google Play Store. Follow the 12 steps below when you have time. **Total time to live: 3-5 days** (assuming no rejections).

---

## ✅ PHASE 1: Account & Credentials Setup (Day 1)

### ☐ **Step 1: Create Google Play Developer Account**
- **What:** Register as Android app developer on Google Play Console
- **Time:** 30 min + 24-48h approval
- **How:**
  1. Go to https://play.google.com/console
  2. Sign in with your Google Account
  3. Pay **$25** (one-time fee)
  4. Complete developer profile (name, email, website optional)
  5. Accept terms and agreements
- **Critical:** Cannot proceed without account approval
- **Status:** ⏳ Pending

---

### ☐ **Step 2: Generate & Backup Signing Keystore**
- **What:** Create signing credentials to sign all releases (permanent for this app)
- **Time:** 15 min
- **How:**
  1. In Play Console → Settings → App signing
  2. Click "Generate upload key"
  3. Save the keystore file securely (keep password too!)
  4. **Backup to external drive or password manager** (critical!)
- **Important:**
  - Keystore is permanent — losing it means can't publish updates
  - Store password somewhere safe (password manager recommended)
- **Keystore Details to Remember:**
  ```
  File Path: [FILL THIS IN]
  Keystore Password: [SECURE - don't write here]
  Key Alias: [FILL THIS IN]
  Key Password: [SECURE - don't write here]
  ```
- **Status:** ⏳ Pending

---

### ☐ **Step 3: Build Signed Release APK**
- **What:** Create production-ready APK with your signing key
- **Time:** 10-15 min initial + 5-10 min build
- **How:**
  1. Open terminal in project directory
  2. Run:
     ```bash
     cd my-app
     php artisan native:run android --release
     ```
  3. When prompted:
     - Enter keystore file path
     - Enter keystore password
     - Confirm key alias and password
  4. Wait for build to complete
- **Expected Output:**
  - File: `nativephp/android/app/build/outputs/apk/release/app-release.apk`
  - Size: ~40-60 MB
- **If Build Fails:**
  - See troubleshooting section below
  - Check CLAUDE.md for known Android fixes
- **Status:** ⏳ Pending

---

## ✅ PHASE 2: Testing on Real Device (Day 1-2)

### ☐ **Step 4: Install APK via Wireless ADB**
- **What:** Deploy signed APK to your Android device to test
- **Time:** 5 min
- **Prerequisites:**
  - Android device on same WiFi
  - Developer Options enabled
  - Wireless debugging enabled in phone settings
- **How:**
  1. On phone: Settings → About → tap Build Number 7x → Developer Options → Wireless Debugging → Enable
  2. Note IP and port from "Wireless Debugging" notification
  3. In terminal:
     ```bash
     adb pair <IP>:<PORT>
     adb connect <IP>:<PORT>
     adb devices  # Should show your device
     ```
  4. Install APK:
     ```bash
     adb install -r nativephp/android/app/build/outputs/apk/release/app-release.apk
     ```
  5. Check phone → should see "AtomicMe" app installed
- **If ADB Fails:**
  - Verify phone IP correct
  - Check firewall not blocking
  - Try USB debugging as fallback (requires cable)
- **Status:** ⏳ Pending

---

### ☐ **Step 5: QA Test All Core Flows**
- **What:** Verify app works on real device before submitting to Google
- **Time:** 30 min
- **Test Checklist:**
  - [ ] **Onboarding:** Launch app → Select identity → Enter name → Tap continue
  - [ ] **Create Habit:** Home → "+ Habit" → Fill 4-step form (Cue, Craving, Response, Reward) → Save
  - [ ] **Complete Habit:** Home → Tap habit to mark complete → Mark toggles
  - [ ] **View Stats:** Tab → Stats screen → See streak, weekly grid, compound chart
  - [ ] **Weekly Review:** Sunday or 7 days after last review → See reflection overlay
  - [ ] **Profile Sheet:** Tap avatar → See identity dashboard → Edit name → Reset data button
  - [ ] **Data Persistence:** Kill app → Relaunch → All data still there
  - [ ] **Offline:** Turn off WiFi → Complete habit → Turn on WiFi → Check synced to backend
  - [ ] **Crash Check:** Monitor logs: `adb logcat | grep -i "error\|exception"` — should see none
- **If Crash Found:**
  - Note the error from logcat
  - Fix in code
  - Rebuild APK (Step 3)
  - Re-test (Step 4-5)
  - **Do not proceed to store setup if crashing**
- **Status:** ⏳ Pending

---

## ✅ PHASE 3: Store Listing Setup (Day 2-4)

### ☐ **Step 6: Create App Entry in Play Console**
- **What:** Register app in Play Console with basic info
- **Time:** 15 min
- **How:**
  1. Play Console → "Create app" button
  2. Choose:
     - Default language: English
     - App name: **AtomicMe**
     - Category: **Health & Fitness**
  3. Accept terms
  4. You're now in app dashboard
- **Status:** ⏳ Pending

---

### ☐ **Step 7: Complete Store Listing (Metadata & Graphics)**
- **What:** Fill in all user-facing information
- **Time:** 1-2 hours (graphics take longest)
- **Fields to Complete:**

#### Short Description (80 char max)
```
Build better habits using Atomic Habits science
```

#### Full Description (4000 char max)
```
AtomicMe helps you build unstoppable habits using the proven Atomic Habits framework.

✨ Key Features:
• Follow the 4 Laws of Habit Change (Cue, Craving, Response, Reward)
• Track daily completions with visual heatmaps
• Watch your streak grow and celebrate milestones
• Get weekly reflection prompts on Sundays
• See your progress with stats and charts
• Track multiple habits simultaneously

🎯 How It Works:
1. Create your habit using the 4 Laws framework
2. Complete it daily to build your streak
3. Reach milestones (7, 14, 21, 30, 60, 90, 100 days) and celebrate
4. Review weekly to reflect on progress
5. Watch your compound effect grow over time

Perfect for anyone looking to build exercise routines, meditation habits, reading goals, productivity habits, or any meaningful behavior change.

Start small. Be consistent. Change your life.
```

#### Screenshots (minimum 2, maximum 8)
- Use actual phone screenshots or emulator
- Recommended: 1080x1920 pixels (portrait)
- Show:
  1. Onboarding (identity selection)
  2. Home screen with today's habits
  3. Stats screen with streaks
  4. Habit detail with heatmap
  5. Milestone celebration (optional)
- **Tip:** Add text overlays explaining each screen
- **Tools:** Use phone's screenshot feature, then upload

#### App Icon (512x512 PNG)
- Should be clear and recognizable at small size
- No transparency (use solid background)
- Matches brand colors
- **Note:** If don't have yet, create simple version with color + "A" letter

#### Feature Graphic (1024x500 pixels)
- Horizontal banner showing app's core value
- Can use Canva (free) or Figma
- Example: Show "Build Better Habits" text + habit icon
- **Note:** This appears on store listing preview

#### Privacy Policy URL (required)
- Will set up in Step 9 below
- Must be public web URL
- Example: `https://yourdomain.com/privacy`

- **Status:** ⏳ Pending

---

### ☐ **Step 8: Configure Permissions & Content Rating**
- **What:** Set age-appropriateness and declare what permissions app uses
- **Time:** 15 min
- **How:**
  1. Play Console → App → Content Rating
  2. Fill out **IARC Questionnaire** (10 questions):
     - "Does your app contain violence?" → No
     - "Does your app contain sexual content?" → No
     - (Answer based on your app features)
  3. Submit → Google auto-assigns rating (e.g., "Everyone" or "3+")
  4. Go to **Policies → Permissions** → Verify listed permissions match what app actually uses
     - Check: Camera, Location, Contacts, etc.
     - Uncheck unused permissions
- **Status:** ⏳ Pending

---

### ☐ **Step 9: Create & Link Privacy Policy**
- **What:** Publish privacy policy explaining how app handles user data
- **Time:** 30-45 min
- **Why Required:** Missing privacy policy = automatic rejection
- **How:**

#### Option A: Create Custom Privacy Policy (recommended)
1. Create file: `my-app/resources/views/privacy.blade.php`
2. Add content:
   ```html
   <h1>Privacy Policy</h1>
   <p><strong>Last Updated:</strong> [Today's Date]</p>

   <h2>Introduction</h2>
   <p>AtomicMe is committed to protecting your privacy. This policy explains how we handle your data.</p>

   <h2>Data We Collect</h2>
   <ul>
     <li>Habit data: Names and descriptions of habits you create</li>
     <li>Completion data: Dates when you complete habits</li>
     <li>Streak information: Calculated from your completion history</li>
     <li>Identity choice: Your selected identity/category (optional)</li>
   </ul>

   <h2>How We Use Data</h2>
   <p>Your data is used only to:</p>
   <ul>
     <li>Store your habits and progress</li>
     <li>Calculate streaks and milestones</li>
     <li>Show you statistics and charts</li>
     <li>Sync data across devices you use</li>
   </ul>

   <h2>Data Storage</h2>
   <p>Data is stored locally on your device (SQLite database) and synced to our secure backend. We use HTTPS encryption for all data transmission.</p>

   <h2>Sharing Your Data</h2>
   <p>We do not sell or share your personal data with third parties.</p>

   <h2>Data Retention</h2>
   <p>You can delete all data at any time through the app's settings. If you delete your account, all associated data is permanently removed within 30 days.</p>

   <h2>Your Rights</h2>
   <p>You can request access to, correction of, or deletion of your data. Contact: [your-email@example.com]</p>

   <h2>Contact Us</h2>
   <p>For privacy questions: [your-email@example.com]</p>
   ```
3. Create route in `my-app/routes/web.php`:
   ```php
   Route::get('/privacy', function () {
       return view('privacy');
   });
   ```
4. Deploy to your backend (or use GitHub Pages, Notion public link)
5. Get public URL (e.g., `https://yourapp.com/privacy`)

#### Option B: Use Privacy Policy Generator (quick)
1. Go to https://www.privacypolicygenerator.info/
2. Fill out simple form
3. Download HTML
4. Host on GitHub Pages or Vercel
5. Get public URL

- **Link in Play Console:**
  1. Play Console → App → Content Rating → Privacy policy URL
  2. Paste URL
  3. Save
  4. **Test:** Click the URL to verify it loads
- **Status:** ⏳ Pending

---

## ✅ PHASE 4: Release & Submission (Day 4-7)

### ☐ **Step 10: Upload APK to Internal Testing Track (First)**
- **What:** Upload your signed APK to Play Console for initial testing
- **Time:** 5 min
- **How:**
  1. Play Console → Your App → Testing → Internal Testing
  2. Click "Create release" button
  3. Upload signed APK:
     - Click "Upload" → select `app-release.apk` from Step 3
     - Wait for upload and verification (1-2 min)
  4. Fill in:
     - **Version name:** `1.0.0`
     - **Version code:** `1`
     - **Release notes:** "Initial release of AtomicMe"
  5. Click "Save"
  6. **Status:** Saved but NOT published yet
- **Status:** ⏳ Pending

---

### ☐ **Step 11: (Optional) Test with Beta Testers**
- **What:** Invite friends/testers to try app before public release
- **Time:** 3-7 days (feedback window)
- **Why:** Catch crashes and bugs before public release
- **How:**
  1. Play Console → Testing → Closed Testing
  2. Click "Create release" → upload same APK as Step 10
  3. In "Testers" section: Add email addresses (Gmail required)
     - Invite 3-5 trusted friends or colleagues
  4. Send them the test link
  5. They install via Play Store (marked as "beta")
  6. Wait 1-2 days for feedback
  7. If bugs found:
     - Fix in code
     - Rebuild APK (Step 3)
     - Re-upload new version to closed testing
  8. If no issues → proceed to Step 12
- **Note:** This step is optional but **recommended** to avoid rejection
- **Status:** ⏳ Pending

---

### ☐ **Step 12: Submit to Production (Go Live!)**
- **What:** Release app to all users on Google Play Store
- **Time:** 5 min to submit + 24-48 hours for Google review
- **How:**
  1. Play Console → Your App → Production
  2. Click "Create release"
  3. Upload same APK from Step 10 (or latest if you updated in Step 11)
  4. Fill in:
     - **Version name:** `1.0.0`
     - **Version code:** `1`
     - **Release notes:** "Initial release of AtomicMe"
  5. **Rollout %:** Select "5%" (staged rollout — safer for first release)
  6. Review all checklist items (Play Console will show any missing info)
  7. Click **"Review and roll out"** button
  8. Confirm submission
  9. **Status:** Submitted for review ✅

- **What Happens Next:**
  - Google's automated system checks app immediately
  - Human review team tests app (usually within 24 hours)
  - You get email with result:
    - ✅ **Approved:** App goes live to 5% users, then ramps to 100% over 1-2 days
    - ❌ **Rejected:** Email explains reason; fix and resubmit (no additional wait)

- **Status:** ⏳ Pending

---

## 📊 Post-Submission Monitoring

Once submitted, track progress here:

| Day | Status | Action | Notes |
|-----|--------|--------|-------|
| Day 1 | ⏳ Submitted | Submitted to Google for review | Check email |
| Day 1-2 | ⏳ In Review | Google running automated + human checks | Monitor Play Console |
| Day 2-3 | ✅ Approved OR ❌ Rejected | If approved, rolling out to users | If rejected, fix and resubmit |
| Day 3-7 | 📈 Ramping | Live for increasing % of users (5% → 25% → 50% → 100%) | Monitor crashes in Play Console → Vitals |
| Day 7+ | 🎉 Live | Available to all Play Store users | Continue monitoring vitals |

### Things to Monitor After Launch:
- **Crashes & ANRs:** Play Console → Vitals → Crash rate should stay <0.1%
- **User Ratings:** Play Console → Ratings → Address negative feedback
- **Reviews:** Read reviews for common complaints; prioritize fixes
- **Performance:** Monitor battery, memory, startup time if available

---

## 🐛 Troubleshooting

### Build Fails at Step 3

**Error: "libphp.a not found" or timeout (300s)**
- See CLAUDE.md → Known Fixes section
- Solution:
  - Increase timeout to 900s in `vendor/nativephp/mobile/src/Traits/PreparesBuild.php`
  - Or manually download PHP binaries from https://bin.nativephp.com

**Error: "SDK directory not found"**
- Set Android SDK path:
  ```bash
  $env:ANDROID_HOME = "C:\Users\[username]\AppData\Local\Android\Sdk"
  ```

**Error: "Keystore password incorrect"**
- Verify password matches Step 2 exactly
- No spaces or special characters missed
- Try: `keytool -list -v -keystore [path-to-keystore]` to test

### ADB Fails at Step 4

**Error: "Device not found" or "no devices"**
- Verify wireless debugging enabled in phone: Settings → Developer Options → Wireless Debugging
- Check IP and port in wireless debugging notification
- Try: `adb pair <IP>:<PORT>` first, then `adb connect`

**Error: "Installation failed: permission denied"**
- Ensure app uninstalled first: `adb uninstall com.atomicme.app` (or actual bundle ID)
- Try: `adb install -r` (replace) not just `adb install`

### App Crashes at Step 5

**Crash on launch:**
- Check logcat: `adb logcat | grep -i "exception\|error\|fatal"`
- Common: Missing native PHP binary, database schema mismatch
- Fix: Rebuild entire app, ensure migrations ran in code

**Crash on specific feature:**
- Test feature in browser first: http://localhost:8000
- Ensure backend API is responding
- Check if data is being synced correctly

### Store Listing Issues at Step 7

**Screenshots not uploading:**
- Verify image format: PNG or JPG
- Verify dimensions: at least 1080x1920 for portrait
- File size: less than 10MB each

**Description too long:**
- Count characters (4000 max for full description)
- Remove filler text, keep benefits clear

### Submission Rejected at Step 12

**"Privacy policy missing or broken link"**
- Go back to Step 9
- Verify URL loads in browser (paste into address bar)
- Update link in Play Console
- Resubmit

**"App crashes during testing"**
- Crash rate too high (>0.1%)
- Google's test devices hit a bug your device didn't
- Fix bug, rebuild, retest on different device
- Resubmit with detailed release notes explaining fix

**"Policy violation: spam/deceptive behavior"**
- Review Play Store policies: https://play.google.com/about/developer-content-policy/
- Ensure description is truthful, not misleading
- Resubmit

---

## 📝 Important Notes

### ⚠️ Critical Reminders
1. **Keystore is permanent** — Losing it means you can't publish updates. Back it up!
2. **Version code always increases** — For next update, use version code 2, then 3, etc.
3. **Privacy policy is non-negotiable** — Missing it = automatic rejection
4. **Test on real device** — Emulator doesn't catch everything; use your phone
5. **Don't skip QA** — Crashes found by Google = 24-hour wait before resubmit

### 📅 Timeline Summary
- **Day 1:** Account setup + build APK + test on device
- **Day 2-3:** Store listing + privacy policy
- **Day 4:** Submit to Google
- **Day 5-7:** Google reviews + approval
- **Total:** 3-7 days to live (usually 5 days if no issues)

### 🔄 For Future Updates
Once app is live, future updates follow same process:
1. Increment version code by 1
2. Rebuild APK
3. Upload to Production track
4. Submit (no need for beta testing if minor update)
5. Usually approved faster second time

---

## 📞 Support & Resources

- **NativePHP Deployment:** https://nativephp.com/docs/mobile/3/getting-started/deployment
- **Google Play Publishing:** https://developer.android.com/studio/publish
- **Play Console Help:** https://support.google.com/googleplay/android-developer
- **Play Store Policies:** https://play.google.com/about/developer-content-policy/
- **NativePHP App Signing:** https://dev.to/michaelishri/how-to-sign-your-nativephp-android-app-for-the-google-play-store-4hac

---

## ✅ Completion Checklist

Track your progress here:

- [ ] Phase 1 Complete (Account + Credentials)
- [ ] Phase 2 Complete (Device Testing)
- [ ] Phase 3 Complete (Store Setup)
- [ ] Phase 4 Complete (Submission)
- [ ] ✅ App is LIVE on Google Play Store!

**Date Started:** [FILL IN]
**Date Completed:** [FILL IN]
**Play Store Link:** [WILL FILL IN AFTER APPROVAL]

---

## 🎉 Next Steps

When you're ready to start:
1. Schedule 1-2 hours for Phase 1 (account setup)
2. Have phone + laptop ready for Phase 2 (testing)
3. Gather screenshots/graphics for Phase 3
4. Submit and wait for approval!

**Good luck! You've got this! 🚀**
