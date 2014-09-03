
Name: app-photo-organizer
Epoch: 1
Version: 1.6.5
Release: 1%{dist}
Summary: Photo Organizer
License: GPLv3
Group: ClearOS/Apps
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}
Requires: app-base

%description
The Photo organizer app automates the process of organizing digital photos that have been copied to various folders - either because of different devices (smartphone, digital camera upload etc.) or different persons.  The result is an easily navigatable folder with filenames that can be identified by the device that took them.

%package core
Summary: Photo Organizer - Core
License: LGPLv3
Group: ClearOS/Libraries
Requires: app-base-core
Requires: app-flexshare-core
Requires: perl-Image-ExifTool >= 9.17

%description core
The Photo organizer app automates the process of organizing digital photos that have been copied to various folders - either because of different devices (smartphone, digital camera upload etc.) or different persons.  The result is an easily navigatable folder with filenames that can be identified by the device that took them.

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/photo_organizer
cp -r * %{buildroot}/usr/clearos/apps/photo_organizer/

install -d -m 755 %{buildroot}/var/clearos/photo_organizer
install -D -m 0744 packaging/photo-organizer %{buildroot}/usr/sbin/photo-organizer
install -D -m 0644 packaging/photo_organizer.conf %{buildroot}/etc/clearos/photo_organizer.conf

%post
logger -p local6.notice -t installer 'app-photo-organizer - installing'

%post core
logger -p local6.notice -t installer 'app-photo-organizer-core - installing'

if [ $1 -eq 1 ]; then
    [ -x /usr/clearos/apps/photo_organizer/deploy/install ] && /usr/clearos/apps/photo_organizer/deploy/install
fi

[ -x /usr/clearos/apps/photo_organizer/deploy/upgrade ] && /usr/clearos/apps/photo_organizer/deploy/upgrade

exit 0

%preun
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-photo-organizer - uninstalling'
fi

%preun core
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-photo-organizer-core - uninstalling'
    [ -x /usr/clearos/apps/photo_organizer/deploy/uninstall ] && /usr/clearos/apps/photo_organizer/deploy/uninstall
fi

exit 0

%files
%defattr(-,root,root)
/usr/clearos/apps/photo_organizer/controllers
/usr/clearos/apps/photo_organizer/htdocs
/usr/clearos/apps/photo_organizer/views

%files core
%defattr(-,root,root)
%exclude /usr/clearos/apps/photo_organizer/packaging
%dir /usr/clearos/apps/photo_organizer
%dir %attr(755,webconfig,webconfig) /var/clearos/photo_organizer
/usr/clearos/apps/photo_organizer/deploy
/usr/clearos/apps/photo_organizer/language
/usr/clearos/apps/photo_organizer/libraries
/usr/sbin/photo-organizer
%config(noreplace) /etc/clearos/photo_organizer.conf
