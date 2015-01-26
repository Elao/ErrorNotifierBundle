# -*- mode: ruby -*-
# vi: set ft=ruby :

VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
    config.vm.box = "ubuntu/trusty64"
    config.vm.network "private_network", ip: "33.33.33.150"
    config.vm.synced_folder ".", "/vagrant",
    	:nfs => (RUBY_PLATFORM =~ /linux/ or RUBY_PLATFORM =~ /darwin/)

    config.vm.provision :shell, :path => "vagrant/upgrade_puppet.sh"

    config.vm.provision :puppet do |puppet|
        puppet.manifests_path = "vagrant/puppet/manifests"
        puppet.module_path = "vagrant/puppet/modules"
        puppet.options = ['--verbose']
    end

    config.vm.provider "virtualbox" do |vb|
        vb.customize ["modifyvm", :id, "--memory", "1024"]
    end
end