==================================
Question2Answer Ajax Comment 1.01b
==================================
-----------
Description
-----------
This is a plugin for **Question2Answer** that provides ajax commenting functionality.

--------
Features
--------
- currently supports only **basic editor**, but format can be set in admin to markdown or html
- inserts new comment into page dynamically without having to reload
- displays slide effects when showing and hiding comments and comment form
- option to flash star as reminder when commenting on answer to own question
- animated close when pressing Esc key
- option to include @username references when clicking reply, via admin/plugins
- switch on and off via admin/plugins
- extra option to show popup reminder when voting on answer to own question

------------
Installation
------------
#. Install Question2Answer_
#. Get the source code for this plugin from github_, either using git_, or downloading directly:

   - To download using git, install git and then type 
     ``git clone git://github.com/NoahY/q2a-comment-ajax.git comment-ajax``
     at the command prompt (on Linux, Windows is a bit different)
   - To download directly, go to the `project page`_ and click **Download**

#. navigate to your site, go to **Admin -> Posting** on your q2a install and make sure '**Default editor for comments:**' is set to '**Basic Editor**'.
#. go to **Admin -> Plugins** and select the '**Enable ajax comment form**' option, then '**Save**'.

.. _Question2Answer: http://www.question2answer.org/install.php
.. _git: http://git-scm.com/
.. _github:
.. _project page: https://github.com/NoahY/q2a-comment-ajax

----------
Disclaimer
----------
This is **beta** code.  It is probably okay for production environments, but may not work exactly as expected.  Refunds will not be given.  If it breaks, you get to keep both parts.

-------
Release
-------
All code herein is Copylefted_.

.. _Copylefted: http://en.wikipedia.org/wiki/Copyleft

---------
About q2A
---------
Question2Answer is a free and open source platform for Q&A sites. For more information, visit:

http://www.question2answer.org/

